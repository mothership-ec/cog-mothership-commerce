<?php

namespace Message\Mothership\Commerce\Order\Transaction\EventListener;

use Message\Mothership\Commerce\Order\Transaction;
use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Entity\Item;
use Message\Mothership\Commerce\Payment;
use Message\Mothership\Commerce\Refund;

use Message\Cog\Event\EventListener as BaseListener;

use Message\Cog\Event\SubscriberInterface;

/**
 * Event listener for voiding transactions.
 *
 * @todo delete payments
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
				array('deleteItems'),
				array('deleteOrders'),
				array('deleteOrderPayments'),
				array('deleteRefunds'),
				array('deleteOrderRefunds'),
				array('returnItemsToStock'),
			),
		);
	}

	/**
	 * Deletes any records of type "item" when a transaction is voided.
	 *
	 * @param Transaction\Event\TransactionalEvent $event
	 */
	public function deleteItems(Transaction\Event\TransactionalEvent $event)
	{
		$transaction = $event->getTransaction();
		$delete      = $this->get('order.item.delete');

		$delete->setTransaction($event->getDbTransaction());

		foreach ($transaction->records->getByType(Item\Item::RECORD_TYPE) as $item) {
			$delete->delete($item);
		}
	}

	/**
	 * Deletes any records of type "order" when a transaction is voided.
	 *
	 * @param Transaction\Event\TransactionalEvent $event
	 */
	public function deleteOrders(Transaction\Event\TransactionalEvent $event)
	{
		$transaction = $event->getTransaction();
		$delete      = $this->get('order.delete');

		$delete->setTransaction($event->getDbTransaction());

		foreach ($transaction->records->getByType(Order\Order::RECORD_TYPE) as $order) {
			$delete->delete($order);
		}
	}

	/**
	 * Deletes any records of type "order-payment" when a transaction is voided.
	 *
	 * @param Transaction\Event\TransactionalEvent $event
	 */
	public function deleteOrderPayments(Transaction\Event\TransactionalEvent $event)
	{
		$transaction = $event->getTransaction();
		$delete      = $this->get('order.payment.delete');

		$delete->setTransaction($event->getDbTransaction());

		foreach ($transaction->records->getByType(Order\Entity\Payment\Payment::RECORD_TYPE) as $payment) {
			$delete->delete($payment);
		}
	}

	/**
	 * Deletes any records of type "refund" when a transaction is voided.
	 *
	 * @param Transaction\Event\TransactionalEvent $event
	 */
	public function deleteRefunds(Transaction\Event\TransactionalEvent $event)
	{
		$transaction = $event->getTransaction();
		$delete      = $this->get('refund.delete');

		$delete->setTransaction($event->getDbTransaction());

		foreach ($transaction->records->getByType(Refund\Refund::RECORD_TYPE) as $refund) {
			$delete->delete($refund);
		}
	}

	/**
	 * Deletes any records of type "order-refund" when a transaction is voided.
	 *
	 * @param Transaction\Event\TransactionalEvent $event
	 */
	public function deleteOrderRefunds(Transaction\Event\TransactionalEvent $event)
	{
		$transaction = $event->getTransaction();
		$delete      = $this->get('order.refund.delete');

		$delete->setTransaction($event->getDbTransaction());

		foreach ($transaction->records->getByType(Order\Entity\Refund\Refund::RECORD_TYPE) as $refund) {
			$delete->delete($refund);
		}
	}

	/**
	 * Create a stock movement to put all items in a transaction that is being
	 * voided back into stock in the stock location they were purchased from.
	 *
	 * @param Transaction\Event\TransactionalEvent $event
	 */
	public function returnItemsToStock(Transaction\Event\TransactionalEvent $event)
	{
		$transaction  = $event->getTransaction();
		$stockManager = $this->get('stock.manager');

		$stockManager->setTransaction($event->getDbTransaction());
		$stockManager->createWithRawNote(true);

		$stockManager->setReason($this->get('stock.movement.reasons')->get('void_transaction'));

		$event->getDbTransaction()->add("
			SET @STOCK_NOTE = CONCAT('Void transaction #', ?i)
		", $transaction->id);

		$stockManager->setNote('@STOCK_NOTE');
		$stockManager->setAutomated(true);

		foreach ($transaction->records->getByType(Item\Item::RECORD_TYPE) as $item) {
			$stockManager->increment($item->getUnit(), $item->stockLocation);
		}
	}
}