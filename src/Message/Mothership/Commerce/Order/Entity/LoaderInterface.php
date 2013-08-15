<?php

namespace Message\Mothership\Commerce\Order\Entity;

use Message\Mothership\Commerce\Order\Order;
use Message\Mothership\Commerce\Order\Loader as OrderLoader;

/**
 * Interface for loading decorators for order entities.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface LoaderInterface
{
	/**
	 * Set the order loader to use.
	 *
	 * Entity loaders will always need an order loader, and vica-versa. So this
	 * approach is used to set the order loader on the entity loader from within
	 * the order loader.
	 *
	 * @param OrderLoader $orderLoader The order loader to use
	 */
	public function setOrderLoader(OrderLoader $orderLoader);

	/**
	 * Get the entities related to a specific order.
	 *
	 * This should always return an array, where the key is the ID of the entity
	 * and the value is the entity object.
	 *
	 * @param  Order  $order The order to get entities for
	 *
	 * @return array         Array of entities where the key is the entity ID
	 */
	public function getByOrder(Order $order);
}