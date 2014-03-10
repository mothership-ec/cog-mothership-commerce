<?php

namespace Message\Mothership\Commerce\Order\Basket\EventListener;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event\Event as OrderEvent;

use Message\User\Event as UserEvents;
use Message\Mothership\Commerce\Order\Event\ValidateEvent;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\Commerce\Order\Entity\Address\Address;
use Message\Cog\Event\Event as BaseEvent;
use Message\Mothership\Ecommerce;
use Message\User\User;
use Message\User\AnonymousUser;

/**
 * Event listeners to attach the current user to their basket order on login;
 * logout and empty basket.
 *
 * The addresses on the order are also set to the user's addresses, if there
 * are any.
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class AttachUserListener extends BaseListener implements SubscriberInterface
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
			),
		);
	}

	/**
	 * Map user and addresses to the basket order when the user logs in
	 *
	 * @param BaseEvent $event Base event object
	 */
	public function addUserToOrder(BaseEvent $event)
	{
		$user   = $this->get('user.current');
		$basket = $this->get('basket');

		// Remove user & addresses from the basket
		$basket->removeUser();
		$basket->clearEntities('addresses');

		// If the user logged out, don't re-populate anything
		if ($user instanceof AnonymousUser) {
			return false;
		}

		$basket->getOrder()->user = $user;

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
			$deliveryAddress->type = 'delivery';
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
			$basket->clearEntities('addresses');
		}
	}
}
