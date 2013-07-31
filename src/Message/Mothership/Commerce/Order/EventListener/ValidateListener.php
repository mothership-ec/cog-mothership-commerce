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
class TotalsListener implements SubscriberInterface
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

	public function checkProperties(Event\ValidateEvent $event)
	{
		if (!$event->getOrder()->status) {
			$event->addError('Order must have a status set');
		}
		else {
			if (!($event->getOrder()->status instanceof Status)) {
				$event->addError('Order status must be an instance of Order\Status\Status');
			}
		}

		if (!$event->getOrder()->type) {
			$event->addError('Order must have a type set');
		}
	}
}