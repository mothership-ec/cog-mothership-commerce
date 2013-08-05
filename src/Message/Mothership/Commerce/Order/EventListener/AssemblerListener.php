<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\User\Event as UserEvents;
use Message\Mothership\Commerce\Order\Event\ValidateEvent;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\Commerce\Order\Entity\Address\Address;

/**
 * Basket Assembler for adding addresses and users to the basket
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class AssemblerListener extends BaseListener implements SubscriberInterface
{

	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(UserEvents\Event::LOGIN => array(
			array('addUserToOrder')
		));
	}

	public function addUserToOrder(UserEvents\Event $event)
	{
		$this->get('basket')->addUser($event->getUser());

		$addressLoader = $this->get('commerce.user.collection');

		if($addressLoader->load()) {
			$delivery = array_pop($addressLoader->getByProperty('type', 'delivery'));
			$billing  = array_pop($addressLoader->getByProperty('type', 'billing'));

			// Map the addresses to the Order Address object
			$deliveryAddress = new Address;
			$deliveryAddress->id = 'delivery';
			$deliveryAddress->order = $this->get('basket')->getOrder();

			foreach ($delivery as $property => $value) {
				$deliveryAddress->{$property} = $value;
			}
			// Save the delivery address
			$this->get('basket')->addAddress($deliveryAddress);

			$billingAddress = new Address;
			$billingAddress->id = 'billing';
			$billingAddress->order = $this->get('basket')->getOrder();
			// Save the billing address
			foreach ($billing as $property => $value) {
				$billingAddress->{$property} = $value;
			}

			$this->get('basket')->addAddress($billingAddress);
		}
	}
}