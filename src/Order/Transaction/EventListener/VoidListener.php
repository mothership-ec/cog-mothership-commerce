<?php

namespace Message\Mothership\Commerce\Order\Transaction\EventListener;

use Message\Mothership\Commerce\Order\Transaction;
use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Entity\Item;

use Message\Cog\Event\EventListener as BaseListener;

use Message\Cog\Event\SubscriberInterface;

/**
 * Event listener for voiding transactions.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class VoidListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			Transaction\Events::VOID => array(
				array('cancelItems'),
				array('cancelOrders'),
				// stock
			),
		);
	}

	/**
	 * Cancels any records of type "item" when a transaction is voided.
	 *
	 * @param Transaction\Event\TransactionalEvent $event
	 */
	public function cancelItems(Transaction\Event\TransactionalEvent $event)
	{
		$itemEdit = $this->get('order.item.edit');

		$itemEdit->setTransaction($event->getDbTransaction());

		$itemEdit->updateStatus(
			$event->getTransaction()->records->getByType(Item\Item::RECORD_TYPE),
			Order\Statuses::CANCELLED
		);
	}

	/**
	 * Cancels any records of type "order" when a transaction is voided.
	 *
	 * @param Transaction\Event\TransactionalEvent $event
	 */
	public function cancelOrders(Transaction\Event\TransactionalEvent $event)
	{
		$transaction = $event->getTransaction();
		$orderEdit   = $this->get('order.edit');

		$orderEdit->setTransaction($event->getDbTransaction());

		foreach ($transaction->records->getByType(Order\Order::RECORD_TYPE) as $order) {
			$orderEdit->updateStatus($order, Order\Statuses::CANCELLED);
		}
	}
}