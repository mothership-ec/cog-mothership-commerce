<?php

namespace Message\Mothership\Commerce\Product\Form;

use Message\Cog\Form\Handler;
use Message\Mothership\Commerce\Product\Product;

class ProductAttributes extends Handler
{
	public function build(Product $product)
	{
		$this->setName('product-details-edit')
			->setAction($this->_container['routing.generator']
				->generate('ms.commerce.product.edit.details.action', array('productID' => $product->id))
			)
			->setMethod('post');

		$this->add('name');
		$this->add('displayName');
		$this->add('description', 'textarea');
		$this->add('shortDescription', 'textarea')
			->val()->optional();
		$this->add('exportDescription', 'textarea')
			->val()->optional();
		$this->add('category', 'datalist', 'Category', array(
			'choices'	=> $this->_getCategories())
		);
		$this->add('weight')
			->val()->float();
		$this->add('tags', 'textarea')
			->val()->optional();
		$this->add('notes', 'textarea')
			->val()->optional();
		$this->add(
			'export_manufacture_country_id',
			'choice',
			$this->_container['translator']->trans('ms.commerce.product.details.export-manufacture-country.label'),
			array(
				'data' 	  => $product->exportManufactureCountryID,
				'choices' => $this->_container['country.list']->all(),
				'attr'    => array('data-help-key' => 'ms.commerce.product.details.export-manufacture-country.help'),
			)
		);

		return $this;
	}

	protected function _getCategories()
	{
		return $this->_container['product.category.loader']->getCategories();
	}
}