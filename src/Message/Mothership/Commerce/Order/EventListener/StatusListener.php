<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Edit;
use Message\Mothership\Commerce\Order\Status;
use Message\Mothership\Commerce\Order\Statuses;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

/**
 * Order event listener for updating the overall status.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class StatusListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::CREATE_START => array(
				array('setDefaultStatus'),
			),
			OrderEvents::ITEM_STATUS_CHANGE => array(
				array('dispatchSetOrderStatusEvent'),
			),
			OrderEvents::SET_STATUS => array(
				array('checkStatus'),
			),
		);
	}

	/**
	 * If no status has been set on the order yet, set it to the default status
	 * (awaiting dispatch).
	 *
	 * @param Event $event The event object
	 */
	public function setDefaultStatus(Event\Event $event)
	{
		if (!$event->getOrder()->status) {
			$event->getOrder()->status = $this->get('order.statuses')->get(Statuses::AWAITING_DISPATCH);
		}
	}

	/**
	 * Dispatch the event to set the order's overall status. This is fired when
	 * an item status changes.
	 *
	 * If the event results in a status code that is different to the order's
	 * existing status code, it is updated.
	 *
	 * @param  Event\TransactionalEvent $event The event object
	 */
	public function dispatchSetOrderStatusEvent(Event\TransactionalEvent $event)
	{
		$statusEvent = $event->getDispatcher()->dispatch(
			OrderEvents::SET_STATUS,
			new Event\SetOrderStatusEvent($event->getOrder())
		);

		$orderStatus = $statusEvent->getStatus();
		$order       = $statusEvent->getOrder();

		// Skip if no status was set
		if (is_null($orderStatus)) {
			return false;
		}

		// Skip if the status hasn't changed
		if ($orderStatus === $order->status->code) {
			return false;
		}

		$edit = $this->get('order.edit');

		$edit->setTransaction($event->getTransaction());
		$edit->updateStatus($order, $orderStatus);
	}

	/**
	 * Update the order's overall status to the appropriate code.
	 *
	 * @param  Event\SetOrderStatusEvent $event The event object
	 */
	public function checkStatus(Event\SetOrderStatusEvent $event)
	{
		$itemStatuses = array_fill_keys(array_keys($this->get('order.item.statuses')->all()), 0);
		$numItems     = $event->getOrder()->items->count();

		// Group items by status
		foreach ($event->getOrder()->items as $item) {
			if (!array_key_exists($item->status->code, $itemStatuses)) {
				$itemStatuses[$item->status->code] = 0;
			}

			$itemStatuses[$item->status->code]++;
		}

		// All items cancelled
		if ($numItems === $itemStatuses[Statuses::CANCELLED]) {
			return $event->setStatus(Statuses::CANCELLED);
		}

		// All items awaiting dispatch
		if ($numItems === $itemStatuses[Statuses::AWAITING_DISPATCH]) {
			return $event->setStatus(Statuses::AWAITING_DISPATCH);
		}

		// All items dispatched
		if ($numItems === $itemStatuses[Statuses::DISPATCHED]) {
			return $event->setStatus(Statuses::DISPATCHED);
		}

		// All items received
		if ($numItems === $itemStatuses[Statuses::RECEIVED]) {
			return $event->setStatus(Statuses::RECEIVED);
		}

		// Any items received
		if ($itemStatuses[Statuses::RECEIVED] > 0) {
			return $event->setStatus(Statuses::PARTIALLY_RECEIVED);
		}

		// Any items dispatched
		if ($itemStatuses[Statuses::DISPATCHED] > 0) {
			return $event->setStatus(Statuses::PARTIALLY_DISPATCHED);
		}

		// Currently being processed
		return $event->setStatus(Statuses::PROCESSING);
	}
}