<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Edit extends Controller
{
	protected $_product;
	protected $_units;

	public function index($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);

		return $this->render('::product:edit', array(
			'product' => $this->_product,
			'form'    => $this->_getForm(),
		));
	}

	public function units($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);
		$this->_units = $this->_product->getUnits()->all();

		$headings = array();
		foreach($this->_units as $unit) {
			foreach ($unit->options as $name => $value) {
				$headings[$name] = $name;
			}
			foreach ($unit->price as $name => $value) {
				$headings[$name] = $name;
			}
		}
		return $this->render('::product:edit-unit', array(
			'headings'=> $headings,
			'locale'  => $this->get('locale'),
			'product' => $this->_product,
			'units'   => $this->_units ,
			'forms'    => $this->_getUnitForm(),
		));
	}

	protected function _getUnitForm()
	{
		$forms = array();
		foreach ($this->_units as $id => $unit) {
			foreach ($unit->price as $type => $value) {
				$forms[$id] = $this->get('form')
					->setName($id)
					->setAction($this->generateUrl('ms.commerce.product.edit.unit.action', array('productID' => $this->_product->id)));

				$forms[$id]->add($type, 'text', $this->trans('ms.commerce.product.price.'.$type),array('attr' => array('value' =>  $value->getPrice('GBP', $this->get('locale')))));
			}
		}

		return $forms;
	}

	public function process($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);

		$form = $this->_getForm();
		if ($form->isValid() && $data = $form->getFilteredData()) {
			$product = $this->_product;

			$product->authorship->update(new DateTimeImmutable, $this->get('user.current'));

			$product->name                       = $data['name'];
			$product->shortDescription           = $data['short_description'];
			$product->displayName                = $data['display_name'];
			$product->year                       = $data['year'];
			$product->taxRate                    = $data['tax_rate'];
			$product->supplierRef                = $data['supplier_ref'];
			$product->weightGrams                = $data['weight_grams'];
			$product->season                     = $data['season'];
			$product->description                = $data['description'];
			$product->fabric                     = $data['fabric'];
			$product->features                   = $data['features'];
			$product->careInstructions           = $data['care_instructions'];
			$product->sizing                     = $data['sizing'];
			$product->notes                      = $data['notes'];
			$product->tags                       = explode(',',$data['tags']);
			$product->exportDescription          = $data['export_description'];
			$product->exportValue                = $data['export_value'];
			$product->exportManufactureCountryID = $data['export_manufacture_country_id'];

			foreach ($data as $key => $value) {
				if(preg_match("/^price/us", $key)) {
					$type = str_replace('price_', '', $key);
					$product->price[$type]->setPrice('GBP', $value, $this->get('locale'));
				}
			}

			$product = $this->get('product.edit')->save($product);
			$product = $this->get('product.edit')->saveTags($product);
			$product = $this->get('product.edit')->savePrices($product);

			if ($product->id) {
				$this->addFlash('success', 'Product updated successfully');
			}
		}

		return $this->redirectToRoute('ms.commerce.product.edit', array('productID' => $this->_product->id));

	}

	protected function _getForm()
	{
		$form = $this->get('form')
			->setName('product-edit')
			->setAction($this->generateUrl('ms.commerce.product.edit.action', array('productID' => $this->_product->id)))
			->setDefaultValues(array(
				'name'                          => $this->_product->name,
				'short_description'             => $this->_product->shortDescription,
				'display_name'                  => $this->_product->displayName,
				'year'                          => $this->_product->year,
				'tax_rate'                      => $this->_product->taxRate,
				'supplier_ref'                  => $this->_product->supplierRef,
				'weight_grams'                  => $this->_product->weightGrams,
				'season'                        => $this->_product->season,
				'description'                   => $this->_product->description,
				'fabric'                        => $this->_product->fabric,
				'features'                      => $this->_product->features,
				'care_instructions'             => $this->_product->careInstructions,
				'sizing'                        => $this->_product->sizing,
				'notes'                         => $this->_product->notes,
				'tags'                          => implode(',',$this->_product->tags),
				'export_description'            => $this->_product->exportDescription,
				'export_value'                  => $this->_product->exportValue,
				'export_manufacture_country_id' => $this->_product->exportManufactureCountryID,
			))
			->setMethod('post');

		$form->add('name', 'text', $this->trans('ms.commerce.product.name'))
			->val()->maxLength(255);

		$form->add('display_name', 'text', $this->trans('ms.commerce.product.display-name'))
			->val()->maxLength(255);

		$form->add('short_description', 'textarea', $this->trans('ms.commerce.product.short-description'));
		$form->add('description', 'textarea', $this->trans('ms.commerce.product.description'))
			->val()->optional();
		$form->add('fabric', 'textarea', $this->trans('ms.commerce.product.fabric'))
			->val()->optional();
		$form->add('features', 'textarea', $this->trans('ms.commerce.product.features'))
			->val()->optional();
		$form->add('care_instructions', 'textarea', $this->trans('ms.commerce.product.care-instructions'))
			->val()->optional();
		$form->add('sizing', 'textarea', $this->trans('ms.commerce.product.sizing'))
			->val()->optional();
		$form->add('notes', 'textarea', $this->trans('ms.commerce.product.notes'))
			->val()->optional();
		$form->add('tags', 'textarea', $this->trans('ms.commerce.product.tags'))
			->val()->optional();

		$form->add('year', 'text', $this->trans('ms.commerce.product.year'))
			->val()->maxLength(4);
		$form->add('tax_rate', 'text', $this->trans('ms.commerce.product.tax-rate'))
			->val()->maxLength(255);
		$form->add('supplier_ref', 'text', $this->trans('ms.commerce.product.supplier-ref'))
			->val()
				->maxLength(255)
				->optional();

		foreach ($this->_product->price as $type => $value) {
			$form->add('price_'.$type, 'text', $this->trans('ms.commerce.product.price.'.$type),array('attr' => array('value' =>  $value->getPrice('GBP', $this->get('locale')))));
		}

		$form->add('weight_grams', 'number', $this->trans('ms.commerce.product.weight-grams'))
			->val()->maxLength(255);
		$form->add('season', 'text', $this->trans('ms.commerce.product.season'))
			->val()->maxLength(255);

		$form->add('export_description', 'textarea', $this->trans('ms.commerce.product.export-description'))
			->val()->optional();
		$form->add('export_value', 'text', $this->trans('ms.commerce.product.export-value'))
			->val()->optional();
		$form->add('export_manufacture_country_id', 'choice', $this->trans('ms.commerce.product.export-manufacture-country'), array(
			'choices' => $this->get('country.list')->getAll(),
			'attr' => array('data-help-key' => 'ms.cms.attributes.access.help'),
		));

		return $form;
	}
}
