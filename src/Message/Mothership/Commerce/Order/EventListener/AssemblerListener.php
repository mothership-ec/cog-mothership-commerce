<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\User\Event as UserEvents;
use Message\Mothership\Commerce\Order\Event\ValidateEvent;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\Commerce\Order\Entity\Address\Address;
use Message\Cog\Event\Event as BaseEvent;
use Message\Mothership\Ecommerce;
use Message\User\User;

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
		return array(
			UserEvents\Event::LOGIN => array(
				array('addUserToOrder')
			),
			UserEvents\Event::LOGOUT => array(
				array('addUserToOrder')
			),
			Ecommerce\Event::EMPTY_BASKET => array(
				array('addUserToOrder'),
			)
		);
	}

	/**
	 * Map user and addresses to the basket order when the user logs in
	 *
	 * @param BaseEvent $event Login event
	 */
	public function addUserToOrder(BaseEvent $event)
	{
		// Add the logged in user to the basket order
		$user = $this->get('user.current');
		$this->get('basket')->addUser($user);
		// Remove any addresses before we add them
		$this->get('basket')->removeAddresses();
		if (!$user instanceof User) {
			return false;
		}

		$addressLoader = $this->get('commerce.user.address.loader');
		// Try and load their addresses
		$delivery = $addressLoader->getByUserAndType($user, 'delivery');
		$billing  = $addressLoader->getByUserAndType($user, 'billing');

		// A billing address is all we need to continue
		if($billing) {
			// If there is no delivery address, set the billing to the delivery
			// address
			if (!$delivery) {
				$delivery = $billing;
			}
			// Map the addresses to the Order Address object
			$deliveryAddress = new Address;
			foreach ($delivery as $property => $value) {
				if ($property == 'authorship') {
					continue;
				}

				$deliveryAddress->{$property} = $value;
			}
			$deliveryAddress->id = 'delivery';
			$deliveryAddress->order = $this->get('basket')->getOrder();

			// Save the delivery address
			$this->get('basket')->addAddress($deliveryAddress);

			$billingAddress = new Address;
			// Save the billing address
			foreach ($billing as $property => $value) {
				if ($property == 'authorship') {
					continue;
				}

				$billingAddress->{$property} = $value;
			}
			$billingAddress->id = 'billing';
			$billingAddress->order = $this->get('basket')->getOrder();
			$this->get('basket')->addAddress($billingAddress);
		} else {
			$this->get('basket')->removeAddresses();
		}
	}
}