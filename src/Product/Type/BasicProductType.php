<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Form\Handler;
use Message\Cog\Field\Factory;

class BasicProductType implements ProductTypeInterface
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

	public function setFields(Factory $factory, Product $product = null)
	{
	}

	public function getProductDisplayName(Product $product = null)
	{

	}
}