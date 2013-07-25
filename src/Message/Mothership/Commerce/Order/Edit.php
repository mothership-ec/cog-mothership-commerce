<?php

namespace Message\Mothership\Commerce\Order;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\Event\Dispatcher;
use Message\Cog\ValueObject\DateTimeImmutable;

class Edit
{
	protected $_query;
	protected $_eventDispatcher;
	protected $_statuses;
	protected $_currentUser;

	public function __construct(DB\Query $query, Dispatcher $eventDispatcher,
		Status\Collection $statuses, UserInterface $currentUser)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_statuses        = $statuses;
		$this->_currentUser     = $currentUser;
	}

	public function updateStatus(Order $order, $statusCode)
	{
		if (!$this->_statuses->exists($statusCode)) {
			throw new \InvalidArgumentException(sprintf('Order status `%s` does not exist', $statusCode));
		}

		$status = $this->_statuses->get($statusCode);

		// Skip if the item is already at this status
		if ($status->code === $order->status->code) {
			return false;
		}

		$order->authorship->update(
			new DateTimeImmutable,
			$this->_currentUser->id
		);

		$this->_query->run('
			UPDATE
				order_summary
			SET
				status_code = :status?i,
				updated_at  = :updatedAt?i,
				updated_by  = :updatedBy?in
			WHERE
				order_id = :id?i
		', array(
			'id'        => $order->id,
			'status'    => $status->code,
			'updatedAt' => $order->authorship->updatedAt(),
			'updatedBy' => $order->authorship->updatedBy(),
		));

		$order->status = clone $status;
		de('updating order status');
		return $this->_dispatcher->dispatch(
			Event::EDIT,
			new Event($order)
		)->getOrder();
	}
}