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
				->generate('ms.commerce.product.edit.attributes.action', array('productID' => $product->id))
			)
			->setMethod('post');

		$this->add('name', 'text', $this->_trans('ms.commerce.product.attributes.name.label'), array(
			'data' => $product->name,
			'attr' => array('data-help-key' => 'ms.commerce.product.attributes.name.help')
		))
			->val()
			->maxLength(255);
		$this->add('display_name', 'text', $this->_trans('ms.commerce.product.attributes.display-name.label'), array(
			'data' => $product->displayName,
			'attr' => array('data-help-key' => 'ms.commerce.product.attributes.display-name.help')
		))
			->val()
			->maxLength(255);
		$this->add('category', 'datalist', $this->_trans('ms.commerce.product.attributes.category.label'), array(
				'choices'	=> $this->_getCategories(),
				'attr'		=> array('data-help-key' => 'ms.commerce.product.attributes.brand.help')
		));
		$this->add('description', 'textarea', $this->_trans('ms.commerce.product.attributes.description.label'), array(
			'data' => $product->description,
			'attr' => array('data-help-key' => 'ms.commerce.product.attributes.description.help')
		))
			->val()
			->optional();
		$this->add('short_description', 'textarea', $this->_trans('ms.commerce.product.attributes.short-description.label'), array(
			'data' => $product->shortDescription,
			'attr' => array('data-help-key' => 'ms.commerce.product.attributes.short-description.help')
		))
			->val()->optional();
		$this->add('export_description', 'textarea', $this->_trans('ms.commerce.product.attributes.export-description.label'), array(
			'data' => $product->exportDescription,
			'attr' => array('data-help-key' => 'ms.commerce.product.attributes.export-description.help')
		))
			->val()
			->optional();
		$this->add('weight_grams', 'number', $this->_trans('ms.commerce.product.details.weight-grams.label'), array(
			'data' => $product->weight,
			'attr' => array(
				'data-help-key' => 'ms.commerce.product.details.weight-grams.help',
			)
		))
			->val()
			->number()
			->optional();
		$this->add('tags', 'textarea', $this->_trans('ms.commerce.product.details.tags.label'), array(
			'data' => implode(',', $product->tags),
			'attr' => array('data-help-key' => 'ms.commerce.product.details.tags.help')
		))
			->val()
			->optional();
		$this->add('notes', 'textarea', $this->_trans('ms.commerce.product.details.notes.label'), array(
			'data' => $product->notes,
			'attr' => array('data-help-key' => 'ms.commerce.product.details.notes.help')
		))
			->val()
			->optional();
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

	protected function _trans($message, array $params = array(), $domain = null, $locale = null)
	{
		return $this->_container['translator']->trans($message, $params, $domain, $locale);

	}

	protected function _getCategories()
	{
		return $this->_container['product.category.loader']->getCategories();
	}
}