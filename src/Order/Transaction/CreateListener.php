<?php

namespace Message\Mothership\Commerce\Order\Transaction;

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
			OrderEvents::CREATE_COMPLETE => array(
				array('orderCreated'),
			),
			OrderEvents::ENTITY_CREATE => array(
				array('entityCreated'),
			),
		);
	}

	public function orderCreated(Event\Event $event)
	{
		$order = $event->getOrder();

		// magic number!
		if($order->status->code >= 0 || $order->status->code === Statuses::PENDING) {
			$transaction = new Transaction;

			$transaction->addRecord($order);

			foreach($order->items as $item) {
				$transaction->addRecord($item);
			}

			foreach($order->payments as $payment) {
				$transaction->addRecord($payment);
			}

			// get type from somewhere
			$transaction->type = ($order->status->code === Statuses::PENDING ? 'contract_initiation' : 'order');

			$this->get('order.transaction.create')->create($transaction);
		}
	}

	public function entityCreated(Event\EntityEvent $event)
	{
		$payment = $event->getEntity();
		if($payment instanceof Payment && $payment->getOrder()->status->code === Statuses::PENDING) {
			$transaction = new Transaction;

			$transaction->addRecord($payment);
			$transaction->type = 'contract_payment';

			$this->get('order.transaction.create')->create($transaction);
		}
	}
}