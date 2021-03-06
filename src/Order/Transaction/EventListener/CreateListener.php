<?php

namespace Message\Mothership\Commerce\Order\Transaction\EventListener;

use Message\Mothership\Commerce\Order\Transaction\Transaction;
use Message\Mothership\Commerce\Order\Transaction\Types;
use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Payment\Payment;
use Message\Mothership\Commerce\Order\Statuses;

use Message\Cog\Event\EventListener as BaseListener;

use Message\Cog\Event\SubscriberInterface;

/**
 * Event listener for transactions
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class CreateListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::CREATE_END => array(
				array('createOrderTransaction'),
			),
			OrderEvents::ENTITY_CREATE_END => array(
				array('createPaymentTransaction'),
			),

		);
	}

	/**
	 * Creates a transaction with records for the order, all items and payments
	 *
	 * @param  Event\Event $event event carrying information about order
	 */
	public function createOrderTransaction(Event\TransactionalEvent $event)
	{
		$order = $event->getOrder();

		if ($order->status->code >= Statuses::AWAITING_DISPATCH || $order->status->code === Statuses::PAYMENT_PENDING) {
			$transaction = new Transaction;

			$transaction->records->add($order);

			foreach ($order->items as $item) {
				$transaction->records->add($item);
			}

			foreach ($order->payments as $payment) {
				$transaction->records->add($payment);
			}

			// $transaction->type =
			// 	($order->status->code === Statuses::PAYMENT_PENDING ? Types::CONTRACT_INITIATION : Types::ORDER);

			$transaction->type = Types::ORDER;

			$this->get('order.transaction.create')
				->setDbTransaction($event->getTransaction())
				->create($transaction);
		}
	}

	/**
	 * Creates a transaction for contract payments when a payment is added to a
	 * contract.
	 *
	 * @param  Event\EntityEvent $event event carrying information about created
	 *                                  entity
	 */
	public function createPaymentTransaction(Event\EntityEvent $event)
	{
		$payment = $event->getEntity();
		$order   = $event->getOrder();

		if (
			$payment instanceof Payment
			&& $order->status->code === Statuses::PAYMENT_PENDING
			// only if order has already been created
			&& null !== $order->id
		) {

			$transaction = new Transaction;

			$transaction->records->add($payment);
			$transaction->type = Types::CONTRACT_PAYMENT;

			$this->get('order.transaction.create')
				->setDbTransaction($event->getTransaction())
				->create($transaction);
		}
	}
}