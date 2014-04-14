<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Form\Handler;
use Message\Cog\Field\Factory;
use Message\Cog\Field\ContentTypeInterface;

/**
 * Interface for product types
 * @todo this thing is obscenely big, my bad. We should probably trim some of the fat out of this
 *
 * Interface ProductTypeInterface
 * @package Message\Mothership\Commerce\Product\Type
 */
interface ProductTypeInterface extends ContentTypeInterface
{
	public function getProductDisplayName(Product $product = null);
}