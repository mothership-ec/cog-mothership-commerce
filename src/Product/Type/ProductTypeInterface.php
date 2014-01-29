<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Form\Handler;

/**
 * Interface for product types
 * @todo this thing is obscenely big, my bad. We should probably trim some of the fat out of this
 *
 * Interface ProductTypeInterface
 * @package Message\Mothership\Commerce\Product\Type
 */
interface ProductTypeInterface
{
	public function getName();

	public function getDisplayName();

	public function getDescription();

	public function setFields();

	public function add($name, $type = null, $label = null, $options = array());

	public function getProductDisplayName(Product $product = null);

	public function setProduct(Product $product);

	public function setDetailsForm(Handler $form);

	public function setAttributesForm(Handler $form);

	public function getDetailsForm();

	public function getAttributesForm();
}