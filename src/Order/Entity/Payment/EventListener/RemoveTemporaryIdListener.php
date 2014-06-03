<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment\EventListener;

use Message\Mothership\Commerce\Order\Entity\Payment\Payment;
use Message\Mothership\Commerce\Order\Events;
use Message\Mothership\Commerce\Order\Event\EntityEvent;

use Message\Cog\Event\SubscriberInterface;

/**
 * Listener to clear the "id" property on `Payment` entity before creation.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class RemoveTemporaryIdListener implements SubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return [
			Events::ENTITY_CREATE => [
				['removeTemporaryIDs'],
			],
		];
	}

	/**
	 * Clears the "id" property on the `Payment` entity before it is created.
	 *
	 * This is because the "id" is sometimes temporarily filled with something
	 * else as a way to identify the payment as unique. For example, voucher
	 * payments have the voucher ID set as the payment ID during assembly.
	 *
	 * @param  EntityEvent $event
	 */
	public function removeTemporaryIDs(EntityEvent $event)
	{
		$entity = $event->getEntity();

		if (!($entity instanceof Payment)) {
			return false;
		}

		// Skip if the ID is a MySQL variable. We need this!
		if ('@' === substr($entity->id, 0, 1)) {
			return false;
		}

		$entity->id = null;
	}
}