<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\Event\Event as BaseEvent;

class Event extends BaseEvent
{
	private $_product;

	public function __construct(Product $product)
	{
		$this->setProduct($product);
	}

	public function setProduct(Product $product)
	{
		$this->_product = $product;
	}

	public function getProduct()
	{
		return $this->_product;
	}
}