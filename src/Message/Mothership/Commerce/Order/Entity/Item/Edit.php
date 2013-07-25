<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Entity\TransactionalDecoratorInterface;
use Message\Mothership\Commerce\Order\Status\Collection as StatusCollection;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\ValueObject\DateTimeImmutable;

class Edit
{
	protected $_query;
	protected $_eventDispatcher;
	protected $_statuses;
	protected $_currentUser;

	public function __construct(DB\Query $query, DispatcherInterface $dispatcher, StatusCollection $statuses, UserInterface $currentUser)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $dispatcher;
		$this->_statuses        = $statuses;
		$this->_currentUser     = $currentUser;
	}

	public function updateStatus(Item $item, $statusCode)
	{
		if (!$this->_statuses->exists($statusCode)) {
			throw new \InvalidArgumentException(sprintf('Order item status `%s` does not exist', $statusCode));
		}

		$status = $this->_statuses->get($statusCode);

		// Skip if the item is already at this status
		if ($status->code === $item->status->code) {
			return false;
		}

		// Get instance of item status (so we have authorship info)
		$status = new Status\Status($status->code, $status->name);

		$status->authorship->create(
			new DateTimeImmutable,
			$this->_currentUser->id
		);

		$this->_query->run('
			INSERT INTO
				order_item_status
			SET
				order_id    = :orderID?i,
				item_id     = :itemID?i,
				status_code = :status?i,
				created_at  = :createdAt?i,
				created_by  = :createdBy?in
		', array(
			'orderID'   => $item->order->id,
			'itemID'    => $item->id,
			'status'    => $status->code,
			'createdAt' => $status->authorship->createdAt(),
			'createdBy' => $status->authorship->createdBy(),
		));

		$item->status = $status;

		$this->_eventDispatcher->dispatch(
			Event::ITEM_STATUS_CHANGE,
			new Event($item->order)
		);

		return $item;
	}
}