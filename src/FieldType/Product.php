<?php

namespace Message\Mothership\Commerce\FieldType;

use Message\Cog\Field\Field;
use Message\Cog\DB\Query;

use Message\Mothership\Commerce\Product\Loader as ProductLoader;

use Message\Cog\Filesystem;
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

	public function __construct(ProductLoader $loader, Helper\ProductList $products)
	{
		$this->_loader    = $loader;
		$this->_products  = $products->getProductList();
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
			$this->_product = $this->_loader->getByID((int) $this->_value);
		}

		return $this->_product;
	}

	public function getFieldOptions()
	{
		$defaults = [
			'choices'       => $this->_products,
			'empty_value'   => 'Please select a product...',
		];

		return array_merge($defaults, parent::getFieldOptions());
	}

	protected function _loadProductNames()
	{
		$result = $this->_query->run("
			SELECT
				product_id,
				name
			FROM
				product
			ORDER BY
				name ASC
		");

		$this->_products = [];

		foreach ($result as $product) {
			$this->_products[$product->product_id] = $product->name;
		}
	}
}