<?php

namespace Message\Mothership\Commerce\Order\Basket\EventListener;

use Message\Mothership\Ecommerce;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\HTTP\Cookie;
use Message\Cog\Event\Event as BaseEvent;

use Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Event listener class for handling the persistence functionality of customer
 * baskets.
 *
 * There are listeners to retrieve the basket from the database based on the
 * customer's cookie; save any changes back to the database; and clear the saved
 * basket data when the customer clears their basket.
 */
class PersistenceListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			KernelEvents::REQUEST => array(
				array('getBasket')
			),
			KernelEvents::RESPONSE => array(
				array('saveBasket'),
			),
			Ecommerce\Event::EMPTY_BASKET => array(
				array('deleteBasket'),
			)
		);
	}

	public function saveBasket(Event\FilterResponseEvent $event)
	{
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return false;
		}

		$order      = $this->get('basket.order');
		$cookieName = $this->get('cfg')->basket->cookieName;
		$token      = $this->get('request')->cookies->get($cookieName);

		// Skip if the order is empty
		if (count($order->items) < 1) {
			// If the cookie was in the request, delete it and clear the cookie
			if ($token) {
				$this->deleteBasket();
				$this->get('http.cookies')->add(
					new Cookie($cookieName, null, new \DateTime('-1 hour'))
				);
			}

			return false;
		}

		// See if a basket already exists.
		$basket = $this->_services['order.basket.loader']->getByToken($token);

		// Update with current order details.
		if($basket) {
			$basketID = $basket->basket_id;
			$this->_services['order.basket.edit']->update($basketID, $order);
		}
		else {
			// Create a new basket
			$basketID = $this->_services['order.basket.create']->create($order);
			$basket = $this->_services['order.basket.loader']->getByID($basketID);
		}

		// Create the cookie
		$token = $this->_services['order.basket.token']->generate($basketID, $basket->created_at);

		$cookieName = $this->_services['cfg']->basket->cookieName;
		$expire = new \DateTime('+' . $this->_services['cfg']->basket->cookieLength);

		$cookie = new Cookie($cookieName, $token, $expire);

		$this->_services['http.cookies']->add($cookie);
	}

	/**
	 * Get a Basket for the cookie.
	 *
	 * @param Event\Event $event
	 * @return bool
	 */
	public function getBasket(Event\GetResponseEvent $event)
	{
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return false;
		}

		// Get the token from the cookie.
		$token = $this->_services['request']->cookies->get($this->_services['cfg']->basket->cookieName);

		// Check if a basket exists for the hash.
		$basket = $this->_services['order.basket.loader']->getByToken($token);

		if($basket) {
			// Get order from basket
			$order = $this->_services['order.basket.loader']->order($basket);

			if($order && !$this->_services['http.session']->get('basket.order')) {
				// Save to session
				$this->_services['http.session']->set('basket.order', $order);
			}
		}
		else {
			// No basket found, so clear the cookie
			$cookieName = $this->_services['cfg']->basket->cookieName;
			$cookie = new Cookie($cookieName, NULL, 1);

			$this->_services['http.cookies']->add($cookie);
		}
	}

	public function deleteBasket()
	{
		$order = $this->_services['basket.order'];

		// Get the token from the cookie
		$token = $this->_services['request']->cookies->get($this->_services['cfg']->basket->cookieName);

		// See if a basket already exists.
		$basket = $this->_services['order.basket.loader']->getByToken($token);

		// Update with current order details.
		if($basket) {
			$basketID = $basket->basket_id;
			$this->_services['order.basket.delete']->delete($basketID);
		}
	}
}