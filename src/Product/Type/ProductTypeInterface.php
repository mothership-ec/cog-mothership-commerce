<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Field\Factory;
use Message\Mothership\Commerce\Product\Product;

interface ProductTypeInterface
{
	public function getName();

	public function getDisplayName();

	public function getDescription();

	public function setFields(Factory $factory);

	public function getProductDisplayName(Product $product);
}