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
 * Cancellation listener for updating dispatches when orders or order items are
 * cancelled.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class CancellationListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return [
			OrderEvents::ITEM_STATUS_CHANGE => [
				['updateDispatches'],
			],
			OrderEvents::STATUS_CHANGE => [
				['cancelDispatches'],
			],
		];
	}

	/**
	 * Cancels all dispatches for an order if the order has been cancelled
	 * 
	 * @param  Event\TransactionalEvent $event Event
	 */
	public function cancelDispatches(Event\TransactionalEvent $event)
	{
		$order = $event->getOrder();
		$trans = $event->getTransaction();

		if (Statuses::CANCELLED === $order->status->code) {
			$dispatchDeleter = $this->get('order.dispatch.delete');
			$dispatchDeleter->setTransaction($trans);

			foreach ($order->dispatches as $dispatch) {
				if ($dispatch->authorship->isDeleted()) {
					continue;
				}

				$dispatchDeleter->delete($dispatch);
			}

			$order->dispatches->clear();
		}
	}

	/**
	 * Updates or deletes dispatches when items of an order are cancelled.
	 * Removes all cancelled items and updates the dispatch.
	 * If all items have been cancelled, the dispatch is deleted.
	 * 
	 * @param  Event\TransactionalEvent $event Event
	 */
	public function updateDispatches(Event\TransactionalEvent $event)
	{
		$order = $event->getOrder();
		$trans = $event->getTransaction();

		$dispatchEdit    = $this->get('order.dispatch.edit');
		$dispatchDeleter = $this->get('order.dispatch.delete');

		$dispatchEdit->setTransaction($trans);
		$dispatchDeleter->setTransaction($trans);

		foreach ($order->dispatches as $dispatch) {
			if ($dispatch->authorship->isDeleted()) {
				continue;
			}

			$updateNecessary = false;

			foreach ($dispatch->items as $item) {
				if (Statuses::CANCELLED === $item->status->code) {
					$dispatch->items->remove($item);
					$updateNecessary = true;
				}
			}

			if ($updateNecessary) {
				$dispatchEdit->update($dispatch);

				if (0 === $dispatch->items->count()) {
					$dispatchDeleter->delete($dispatch);					
				}
			}
		}
	}
}