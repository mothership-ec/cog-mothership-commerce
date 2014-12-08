<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Form\Handler;
use Message\Cog\Field\Factory;
use Message\Cog\Field\ContentTypeInterface;

/**
 * Interface for product types
 *
 * Interface ProductTypeInterface
 * @package Message\Mothership\Commerce\Product\Type
 */
interface ProductTypeInterface extends ContentTypeInterface
{
	public function getProductDisplayName(Product $product);

	public function getName();

	public function getDescription();

	public function setFields(Factory $factory, Product $product = null);
}