<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Status\Status;

use Message\Cog\Event\SubscriberInterface;

/**
 * Order event listener for validating the order before it is created.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ValidateListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(OrderEvents::CREATE_VALIDATE => array(
			array('checkProperties'),
		));
	}

	/**
	 * Run basic validation on the order before it is created.
	 *
	 * @param  Event\ValidateEvent $event The event object
	 */
	public function checkProperties(Event\ValidateEvent $event)
	{
		$order = $event->getOrder();

		if (!$order->status) {
			$event->addError('Order must have a status set');
		}
		else {
			if (!($order->status instanceof Status)) {
				$event->addError('Order status must be an instance of Order\Status\Status');
			}
		}

		if (!$order->type) {
			$event->addError('Order must have a type set');
		}

		if (!$order->currencyID) {
			$event->addError('Order must have a currency ID');
		}
	}
}