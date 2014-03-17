<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\DB\Query;
use Message\Cog\Field\Factory;

class ApparelProductType implements ProductTypeInterface
{
	public function getName()
	{
		return 'apparel';
	}

	public function getDisplayName()
	{
		return 'Apparel product';
	}

	public function getDescription()
	{
		return 'A wearable item e.g. clothing';
	}

	public function setFields(Factory $factory)
	{
		
	}

	public function getProductDisplayName(Product $product = null)
	{

	}
}