<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;

use Message\Mothership\Commerce\Order\Entity\Address\Address;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

/**
 * Event listener for installations that charge VAT within the EU to customers.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class VatListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::CREATE_START => array(
				array('setTaxable', 100),
			),
		);
	}

	/**
	 * Set the order to taxable if the delivery country is within the EU.
	 * Otherwise, set it to not taxable.
	 *
	 * @param Event $event The event object
	 */
	public function setTaxable(Event\Event $event)
	{
		$order           = $event->getOrder();
		$deliveryCountry = $order->getAddress(Address::DELIVERY)->countryID;

		// TODO: make this work for countries in the EU also
		if ('GB' === $deliveryCountry) {
			$order->taxable = true;
		}
		else {
			$order->taxable = false;
		}
	}
}