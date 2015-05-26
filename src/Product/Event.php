<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\Event\Event as BaseEvent;

/**
 * Class Event
 * @package Message\Mothership\Commerce\Product
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class Event extends BaseEvent
{
	/**
	 * @var Product
	 */
	private $_product;

	public function __construct(Product $product)
	{
		$this->setProduct($product);
	}

	/**
	 * @param Product $product
	 */
	public function setProduct(Product $product)
	{
		$this->_product = $product;
	}

	/**
	 * @return Product
	 */
	public function getProduct()
	{
		return $this->_product;
	}
}