<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Payment\Payment;
use Message\Mothership\Commerce\Order\Status\Statuses;

use Message\Cog\Event\SubscriberInterface;

/**
 * Event listener for transactions
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class EventListener implements SubscriberInterface
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
				array('enitityCreated'),
			),
		);
	}

	public function orderCreated(Event\Event $event)
	{
		$order = $event->getOrder();
		// magic number!
		if($order->status->code > 0) {

		} elseif($order->status->code === Statuses::PENDING) {

		}
	}

	public function entityCreated(Event\EntityEvent $event)
	{
		$payment = $event->getEntity();
		if($payment instanceof Payment && $payment->getOrder()->status->code === Statuses::PENDING) {

		}
	}
}