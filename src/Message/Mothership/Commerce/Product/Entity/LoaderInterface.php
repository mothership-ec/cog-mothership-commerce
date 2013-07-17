<?php

namespace Message\Mothership\Commerce\Order\Entity;

use Message\Mothership\Commerce\Order\Order;

/**
 * Interface for loading decorators for order entities.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface LoaderInterface
{
	/**
	 * Get the entities related to a specific product.
	 *
	 * This should always return an array, where the key is the ID of the entity
	 * and the value is the entity object.
	 *
	 * @param  Product  $product The order to get entities for
	 *
	 * @return array         Array of entities where the key is the entity ID
	 */
	public function getByProduct(Product $order);
}