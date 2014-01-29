<?php

namespace Message\Mothership\Commerce\Product\Form;

use Message\Cog\Form\Handler;

class ProductAttributes extends Handler
{
	public function build()
	{
		$this->add('name');
		$this->add('displayName');
		$this->add('description', 'textarea');
		$this->add('shortDescription', 'textarea')
			->val()->optional();
		$this->add('exportDescription', 'textarea')
			->val()->optional();
		$this->add('category', 'datalist', 'Category', $this->_getCategories());
		$this->add('weight')
			->val()->float();
		$this->add('tags', 'textarea')
			->val()->optional();
		$this->add('notes', 'textarea')
			->val()->optional();
		$this->add(
			'export_manufacture_country_id',
			'choice',
			$this->trans('ms.commerce.product.details.export-manufacture-country.label'),
			array(
				'data' 	  => $this->_product->exportManufactureCountryID,
				'choices' => $this->get('country.list')->all(),
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