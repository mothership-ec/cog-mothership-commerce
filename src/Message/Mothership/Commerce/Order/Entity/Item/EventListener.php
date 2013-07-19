<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Event;

use Message\Cog\Event\SubscriberInterface;

/**
 * Order item event listener.
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
		return array(Event::CREATE_START => array(
			array('calculateTax'),
		));
	}

	/**
	 * Calculate the gross, tax and net amounts for each item in an order before
	 * it gets created in the database.
	 *
	 * @param Event $event The event object
	 */
	public function calculateTax(Event $event)
	{
		foreach ($event->getOrder()->items as $item) {
			$item->gross = round($item->listPrice - $item->discount, 2);
			$item->tax   = round(($item->gross / (100 + $item->taxRate)) * $item->taxRate, 2);
			$item->net   = round($item->gross - $item->tax, 2);
		}
	}
}