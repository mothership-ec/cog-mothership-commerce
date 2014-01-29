<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;

class BasicProductType extends AbstractProductType
{
	public function getName()
	{
		return 'basic';
	}

	public function getDisplayName()
	{
		return 'Basic product';
	}

	public function getDescription()
	{
		return 'A product with only the essential information';
	}

	public function setFields()
	{
		return $this;
	}

	public function getProductDisplayName(Product $product = null)
	{
		if (!$product && !$this->_product) {
			throw new \LogicException('Product not set');
		}
	}
}