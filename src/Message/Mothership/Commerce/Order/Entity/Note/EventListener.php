<?php

namespace Message\Mothership\Commerce\Order\Entity\Note;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;

/**
 * Order item event listener.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	protected $_defaultStatus;

	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::ENTITY_CREATE => array(
				array('sendCustomerNotification'),
			),
		);
	}

	/**
	 * Send a customer a notification.
	 *
	 * @param Event\EntityEvent $event
	 */
	public function sendCustomerNotification(Event\EntityEvent $event)
	{
		$note = $event->getEntity();

		if (! $note instanceof Note or ! $note->customerNotified) {
			return false;
		}

		$factory = $this->get('mail.factory.order.note.notification')
				->set('order', $event->getOrder())
				->set('note', $note);

		$this->get('mail.dispatcher')->send($factory->getMessage());
	}
}