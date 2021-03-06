<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Status\Collection as StatusCollection;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\ValueObject\DateTimeImmutable;

class Edit implements DB\TransactionalInterface
{
	protected $_query;
	protected $_transOverridden = false;

	protected $_eventDispatcher;
	protected $_statuses;
	protected $_currentUser;

	public function __construct(DB\Transaction $query, DispatcherInterface $dispatcher,
		StatusCollection $statuses, UserInterface $currentUser)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $dispatcher;
		$this->_statuses        = $statuses;
		$this->_currentUser     = $currentUser;
	}

	/**
	 * Sets transaction and sets $_transOverridden to true.
	 *
	 * @param  DB\Transaction $trans transaction
	 * @return Create                $this for chainability
	 */
	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query           = $trans;
		$this->_transOverridden = true;

		return $this;
	}

	/**
	 * Update the status of an item or items.
	 *
	 * @param  Item|Collection|array $items Item, array of items or collection
	 *                                      of items
	 * @param  int              $statusCode Status code to set
	 *
	 * @return Edit                         Returns $this for chainability
	 *
	 * @throws \InvalidArgumentException If the item status supplied is not
	 *                                   set on the status collection
	 * @throws \InvalidArgumentException If no valid Item instances are passed
	 * @throws \InvalidArgumentException If a non-falsey value that is not an
	 *                                   instance of Item is passed as an item
	 */
	public function updateStatus($items, $statusCode)
	{
		if (!$this->_statuses->exists($statusCode)) {
			throw new \InvalidArgumentException(sprintf('Order item status `%s` does not exist', $statusCode));
		}

		$status = $this->_statuses->get($statusCode);

		if (!is_array($items) && !($items instanceof Collection)) {
			$items = array($items);
		}

		// Filter out any falsey values
		$items  = ($items instanceof Collection) ? $items->all() : $items;
		$items  = array_filter($items);
		$orders = [];

		// Throw exception if we don't have any items
		if (empty($items)) {
			throw new \InvalidArgumentException('No items passed to `updateStatus()`');
		}

		foreach ($items as $key => $item) {
			if (!($item instanceof Item)) {
				$type = gettype($item);
				if ($type == 'object') {
					$type = get_class($item);
				}
				throw new \InvalidArgumentException(sprintf('Unexpected value: expected order item instance - "' . $type . '"'));
			}

			// Skip if the item is already at this status
			if ($status->code === $item->status->code) {
				continue;
			}

			// Get instance of item status (so we have authorship info)
			$status = new Status\Status($status->code, $status->name);

			$status->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);

			$this->_query->add('
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

			// Collect the order if it hasn't been collected yet
			if (!array_key_exists($item->order->id, $orders)) {
				$orders[$item->order->id] = $item->order;
			}
		}

		// Dispatch an event for each individual order
		foreach ($orders as $order) {
			$event = new Event\TransactionalEvent($order);
			$event->setTransaction($this->_query);

			$this->_eventDispatcher->dispatch(
				OrderEvents::ITEM_STATUS_CHANGE,
				$event
			);
		}

		if (!$this->_transOverridden) {
			$this->_query->commit();
		}

		return $this;
	}
}