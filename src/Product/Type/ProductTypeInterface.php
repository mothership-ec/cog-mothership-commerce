<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Form\Handler;

interface ProductTypeInterface
{
	public function __construct(ContainerInterface $container);

	public function getName();

	public function getDisplayName();

	public function getDescription();

	public function setFields();

	public function add($name, $type = null, $label = null, $options = array());

	public function getProductDisplayName(Product $product);

	public function setDetailsForm(Handler $form);

	public function setAttributesForm(Handler $form);
}