<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Edit;
use Message\Mothership\Commerce\Order\Status;
use Message\Mothership\Commerce\Order\Statuses;

use Message\Cog\Event\SubscriberInterface;

/**
 * Order event listener for updating the overall status.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class StatusListener implements SubscriberInterface
{
	protected $_statuses;
	protected $_orderEdit;

	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			Event::CREATE_START => array(
				array('setDefaultStatus'),
			),
			Event::ITEM_STATUS_CHANGE => array(
				array('checkStatus'),
			),
		);
	}

	public function __construct(Status\Collection $statuses, Edit $orderEdit)
	{
		$this->_statuses  = $statuses;
		$this->_orderEdit = $orderEdit;
	}

	/**
	 * If no status has been set on the order yet, set it to the default status
	 * (awaiting dispatch).
	 *
	 * @param Event $event The event object
	 */
	public function setDefaultStatus(Event $event)
	{
		if (!$event->getOrder()->status) {
			$event->getOrder()->status = $this->_statuses->get(Statuses::AWAITING_DISPATCH);
		}
	}

	public function checkStatus(Event $event)
	{
		$itemStatuses = array_fill_keys($this->_statuses->all(), 0);
		$numItems     = $event->getOrder()->items->count();

		// Group items by status
		foreach ($event->getOrder()->items as $item) {
			$itemStatuses[$item->status->code]++;
		}

		// All items awaiting dispatch
		if ($numItems === count($itemStatuses[Statuses::AWAITING_DISPATCH])) {
			$newStatus = Statuses::AWAITING_DISPATCH;
		}
		// All items dispatched
		elseif ($numItems === count($itemStatuses[Statuses::DISPATCHED])) {
			$newStatus = Statuses::DISPATCHED;
		}
		// All items received
		elseif ($numItems === count($itemStatuses[Statuses::RECEIVED])) {
			$newStatus = Statuses::RECEIVED;
		}
		// Any items received
		elseif (array_key_exists(Statuses::RECEIVED, $itemStatuses) && !empty($itemStatuses[Statuses::RECEIVED])) {
			$newStatus = Statuses::PARTIALLY_RECEIVED;
		}
		// Any items dispatched
		elseif (array_key_exists(Statuses::DISPATCHED, $itemStatuses) && !empty($itemStatuses[Statuses::DISPATCHED])) {
			$newStatus = Statuses::PARTIALLY_DISPATCHED;
		}
		// Currently being processed
		else {
			$newStatus = Statuses::PROCESSING;
		}

		// Update status if it has changed
		if ($newStatus !== $event->getOrder()->status->code) {
			$event->setOrder(
				$this->_orderEdit->updateStatus($event->getOrder(), $newStatus)
			);
		}
	}
}