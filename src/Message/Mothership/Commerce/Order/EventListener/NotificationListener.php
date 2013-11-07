<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Entity\Note;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

/**
 * Order event listener for sending customer notifications.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class NotificationListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::DISPATCH_SHIPPED => array(
				array('sendDispatchShippedNotification'),
			),
			OrderEvents::ENTITY_CREATE => array(
				array('sendCustomerNotification'),
			),
		);
	}

	/**
	 * Send a customer a notification the dispatch has been shipped.
	 *
	 * @param  Event\DispatchEvent $event
	 */
	public function sendDispatchShippedNotification(Event\DispatchEvent $event)
	{
		$dispatch = $event->getDispatch();
		$order    = $event->getOrder();
		$merchant = $this->get('cfg')->merchant;

		$message = $this->get('mail.message');

		$message->setTo($order->user->email, $order->user->getName());
		$message->setSubject('Your ' . $merchant->companyName . ' order ' . $order->orderID . ' has shipped');
		$message->setView('Message:Mothership:Commerce::mail:order:dispatch:shipped-notification', array(
			'dispatch' => $dispatch,
			'order'    => $order,
			'merchant' => $merchant,
		));

		$dispatcher = $this->get('mail.dispatcher');

		$dispatcher->send($message);
	}

	/**
	 * Send a customer a notification.
	 *
	 * @param Event\EntityEvent $event
	 */
	public function sendCustomerNotification(Event\EntityEvent $event)
	{
		$note = $event->getEntity();

		if (! $note instanceof Note\Note or ! $note->customerNotified) {
			return false;
		}

		$order    = $event->getOrder();
		$merchant = $this->get('cfg')->merchant;

		$message = $this->get('mail.message');

		$message->setTo($order->user->email, $order->user->getName());
		$message->setSubject('Updates to your ' . $merchant->companyName . ' order ' . $order->orderID);
		$message->setView('Message:Mothership:Commerce::mail:order:note:customer-notification', array(
			'note'     => $note,
			'order'    => $order,
			'merchant' => $merchant,
		));

		$dispatcher = $this->get('mail.dispatcher');

		$dispatcher->send($message);
	}
}