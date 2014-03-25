<?php

namespace Message\Mothership\Commerce\FieldType;

use Message\Cog\Field\Field;

use Message\Mothership\Commerce\Product\Loader as ProductLoader;

use Message\Cog\Filesystem;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Symfony\Component\Form\FormBuilder;

/**
 * A field for a product from the products database.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Product extends Field
{
	protected $_product;
	protected $_products;
	protected $_loader;

	public function __construct(ProductLoader $loader)
	{
		$this->_loader = $loader;
	}

	public function getFieldType()
	{
		return 'product';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'choice', $this->getFieldOptions());
	}

	public function getFormType()
	{
		return 'choice';
	}

	public function getProduct()
	{
		if (null === $this->_product) {
			$this->_product = $this->_services['product.loader']->getByID((int) $this->_value);
		}

		return $this->_product;
	}

	public function getFieldOptions()
	{
		$defaults = [
			'choices'       => $this->_getChoices(),
			'empty_value'   => 'Please select a product...',
		];

		return array_merge($defaults, parent::getFieldOptions());
	}

	protected function _getChoices()
	{
		$choices = array();

		foreach ($this->_getProducts() as $product) {
			$choices[$product->id] = $product->displayName ?: $product->name;
		}

		return $choices;
	}

	protected function _getProducts()
	{
		if (null === $this->_products) {
			$this->_products = $this->_loader->getAll();
		}

		return $this->_products;
	}
}