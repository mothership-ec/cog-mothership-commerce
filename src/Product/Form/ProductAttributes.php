<?php

namespace Message\Mothership\Commerce\Product\Form;

use Message\Cog\Form\Handler;
use Message\Mothership\Commerce\Product;

class ProductAttributes extends Handler
{
	public function build(Product\Product $product)
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
			->optional()
			->maxLength(255);
		$this->add('sort_name', 'text', $this->_trans('ms.commerce.product.attributes.sort-name.label'), array(
			'data' => $product->sortName,
			'attr' => array('data-help-key' => 'ms.commerce.product.attributes.sort-name.help')
		))
			->val()
			->optional()
			->maxLength(255);
		$this->add('category', 'datalist', $this->_trans('ms.commerce.product.attributes.category.label'), array(
			'choices'	=> $this->_getCategories(),
			'data'		=> $product->category,
			'attr'		=> array('data-help-key' => 'ms.commerce.product.attributes.category.help')
		));
		$this->add('brand', 'datalist', $this->_trans('ms.commerce.product.attributes.brand.label'), array(
			'data'    => $product->brand,
			'choices' => $this->_getBrands(),
			'attr'    => array('data-help-key' => 'ms.commerce.product.attributes.brand.help')
		))
			->val()
			->maxLength(255)
			->optional();
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
		$this->add(
			'export_code',
			'text',
			$this->_container['translator']->trans('ms.commerce.product.details.export-code.label'),
			[
				'data' => $product->getExportCode(),
				'attr' => ['data-help-key' => 'ms.commerce.product.details.export-code.help']
			]
		)
			->val()
			->optional()
		;
		$this->add('supplier_ref', 'text', $this->_trans('ms.commerce.product.details.supplier-ref.label'), array(
			'data' => $product->supplierRef,
			'attr' => array('data-help-key' => 'ms.commerce.product.details.supplier-ref.help')
		))
			->val()
			->maxLength(255)
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
			'data' => implode(', ', $product->tags),
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

		$typeChoices = [];

		foreach ($this->_container['product.types'] as $type) {
			$typeChoices[$type->getName()] = $type->getDisplayName();
		}

		$this->add('product_type',
			'choice',
			$this->_container['translator']->trans('ms.commerce.product.details.product-type.label'),
			[
				'data'        => $product->type->getName(),
				'choices'     => $typeChoices,
			]
		);

		return $this;
	}

	protected function _trans($message, array $params = array(), $domain = null, $locale = null)
	{
		return $this->_container['translator']->trans($message, $params, $domain, $locale);

	}

	protected function _getCategories()
	{
		return $this->_container['product.category.loader']->getAll();
	}


	protected function _getBrands()
	{
		return $this->_container['db.query.builder.factory']
			->getQueryBuilder()
			->select('brand', true)
			->from('product')
			->getQuery()
			->run()
			->flatten();
	}
}