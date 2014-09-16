<?php

namespace Message\Mothership\Commerce\Product;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\DB\Entity\EntityLoaderInterface;

/**
 * Interface for loading decorators for product entities.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
interface ProductEntityLoaderInterface extends EntityLoaderInterface
{
	/**
	 * Get the entities related to a specific product.
	 *
	 * This should always return an array, where the key is the ID of the entity
	 * and the value is the entity object.
	 *
	 * @param  Product  $product The order to get entities for
	 *
	 * @return array             Array of entities where the key is the entity ID
	 */
	public function getByProduct(Product $product);

	/**
	 * Sets product loader on entity loader to always make it possible to
	 * load the product from the entity.
	 *
	 * @param Loader $productLoader
	 */
	public function setProductLoader(Loader $productLoader);
}