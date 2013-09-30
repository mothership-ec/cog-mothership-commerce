<?php

namespace Message\Mothership\Commerce\Order\Entity\Address;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Status\Status as BaseStatus;

use Message\Cog\Event\SubscriberInterface;

/**
 * Order address event listener.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::CREATE_VALIDATE => array(
				array('checkWebOrderAddresses'),
				array('checkNoDuplicateAddresses'),
			),
		);
	}

	public function checkWebOrderAddresses(Event\ValidateEvent $event)
	{
		if ('web' !== $event->getOrder()->type) {
			return;
		}

		if (count($event->getOrder()->addresses->getByProperty('type', Address::DELIVERY)) < 1) {
			$event->addError('Web orders must have an address of `delivery` type');
		}

		if (count($event->getOrder()->addresses->getByProperty('type', Address::BILLING)) < 1) {
			$event->addError('Web orders must have an address of `billing` type');
		}
	}

	public function checkNoDuplicateAddresses(Event\ValidateEvent $event)
	{
		$addresses = array();

		foreach ($event->getOrder()->addresses as $address) {
			if (!array_key_exists($address->type, $addresses)) {
				$addresses[$address->type] = 0;
			}

			$addresses[$address->type]++;
		}

		foreach ($addresses as $type => $num) {
			if ($num > 1) {
				$event->addError(sprintf(
					'An order may only have one address per type, there are %i `%s` addresses',
					$num,
					$type
				));
			}
		}
	}
}