<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Edit extends Controller
{
	protected $_product;
	protected $_units;

	/**
	 * Show product edit screen
	 *
	 * @param  int 		$productID  ProductID to load
	 *
	 * @return Response 			Output for view
	 */
	public function index($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);

		return $this->render('::product:edit', array(
			'product' => $this->_product,
			'form'    => $this->_getProductForm(),
		));
	}

	/**
	 * Show product units with for for editing them
	 *
	 * @param  int 		$productID 	ProductID to load
	 */
	public function units($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);
		$this->_units = $this->_product->getUnits()->all();

		$headings = array();
		foreach($this->_units as $unit) {
			foreach ($unit->options as $name => $value) {
				$headings[$name] = ucfirst($name);
			}
		}

		return $this->render('::product:edit-unit', array(
			'headings'    => $headings,
			'locale'      => $this->get('locale'),
			'product'     => $this->_product,
			'units'       => $this->_units ,
			'form'        => $this->_getUnitForm(),
			'addForm'     => $this->_addNewUnitForm(),
			'optionValue' => $this->get('option.loader')->getAllOptionValues(),
		));
	}

	/**
	 * Method for unit stock interface
	 *
	 * @param  int 	$productID 	ProductID to load
	 */
	public function stock($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);
		$this->_units = $this->_product->getAllUnits()->all();

		return $this->render('::product:edit-stock', array(
			'locale'  => $this->get('locale'),
			'product' => $this->_product,
			'units'   => $this->_units,
			'form'	  => $this->_getUnitStockForm(),
		));
	}

	/**
	 * Return the form for editing unit stock levels
	 *
	 * @return Handler Form Handler for stock editing
	 */
	public function _getUnitStockForm()
	{
		// Make an overall form
		$mainForm = $this->get('form')
			->setName('units-stock')
			->setAction(
				$this->generateUrl('ms.commerce.product.edit.stock.action',
					array(
						'productID' => $this->_product->id
					))
			);
		// Create a nested form for each unit
		foreach ($this->_units as $id => $unit) {
			$form = $this->get('form')
				->setName($id)
				->addOptions(array(
					'auto_initialize' => false,
			));

			$stockForm = $this->get('form')
				->setName('stock')
				->addOptions(array(
					'auto_initialize' => false,
			));

			foreach ($unit->stock as $type => $value) {
				$stockForm->add(
					'location_'.$type,
					'text',
					$this->trans('ms.commerce.product.stock-location.'.strtolower($type)),
					array('attr' => array(
							'value' =>  $value
					)))->val()->optional();
			}

			$form->add($stockForm->getForm(), 'form');
			$mainForm->add($form->getForm(), 'form');
		}

		return $mainForm;
	}

	/**
	 * Return the unit editing form
	 *
	 * @return Handler 		Form Handler for editing units and their prices
	 */
	protected function _getUnitForm()
	{
		// Main form
		$mainForm = $this->get('form')
			->setName('units-edit')
			->setAction($this->generateUrl('ms.commerce.product.edit.units.action', array('productID' => $this->_product->id)));
		// Create a nested form for each of the units in this product
		foreach ($this->_units as $id => $unit) {
			$form = $this->get('form')
				->setName($id)
				->setDefaultValues(array(
					'visible' => (bool) $unit->visible,
				))
				->addOptions(array(
					'auto_initialize' => false,
				));

			// create a nested form for prices so we can have name="units-edit[unitID][price][retail]"
			$priceForm = $this->get('form')
				->setName('price')
				->addOptions(array(
					'auto_initialize' => false,
			));

			foreach ($unit->price as $type => $value) {
				$priceForm->add($type, 'text',$this->trans('ms.commerce.product.price-sans.'.strtolower($type)), array('attr' => array('value' =>  $value->getPrice('GBP', $this->get('locale')))))
					->val()->optional();
			}

			// Add the price form to the parent form
			$form->add($priceForm->getForm(), 'form');

			// Work out the default options which should be 'selected' in the option drop downs
			$defaults = array();
			foreach ($unit->options as $type => $value) {
				$defaults[$type] = $value;
			}

			// create a nested form for the unit options so we can have name="units-edit[unitID][options][colour]"
			$optionForm = $this->get('form')
				->setName('options')
				->setDefaultValues($defaults)
				->addOptions(array(
					'auto_initialize' => false,
			));

			// Build the options
			foreach ($unit->options as $type => $value) {
				// populate the select menu options by getting all available options from the DB
				$choices = array();
				foreach ($this->get('option.loader')->getByName($type) as $choice) {
					$choice = trim($choice);
					$choices[$choice] = $choice;
				}

				$optionForm->add($type, 'choice','', array('choices' => $choices))
					->val()->optional();
			}
			// Add the option forms to the parent form
			$form->add($optionForm->getForm(), 'form');

			// Populate the rest of the editbale unit attributes
			$form->add('sku', 'text','', array('attr' => array('value' =>  $unit->sku)));
			$form->add('weight', 'text','', array('attr' => array('value' =>  $unit->weightGrams)))
				->val()->optional();
			$form->add('visible', 'checkbox','',array('attr' => array('value' =>  $unit->visible)))
				->val()->optional();
			$form->add('delete', 'checkbox','')
				->val()->optional();

			// Add the unit form to the main form
			$mainForm->add($form->getForm(), 'form');
		}

		return $mainForm;
	}

	/**
	 * Return the form for adding a new unit to a product
	 */
	protected function _addNewUnitForm()
	{
		$headings = array();
		foreach ($this->get('option.loader')->getAllOptionNames() as $name => $value) {
				$headings[$value] = ucfirst($value);
		}

		$form = $this->get('form')
			->setName('new')
			->setAction($this->generateUrl('ms.commerce.product.edit.units.create.action', array('productID' => $this->_product->id)))
			->setDefaultValues(array(
				'visible' => false,
		));

		$form->add('sku', 'text','',array('attr' => array('list' => 'option_value', 'placeholder' => 'SKU')));
		$form->add('weight', 'text','');

		$form->add('option_name_1', 'choice','Option name 1',
			array(
				'choices' => $headings,
				'empty_value' => 'Select option',
			)
		);
		$form->add('option_value_1', 'text','Option value', array('attr' => array('list' => 'option_value', 'placeholder' => 'Value')));

		$form->add('option_name_2', 'choice','Option name 2',
			array(
				'choices' => $headings,
				'empty_value' => 'Select option',
			)
		);
		$form->add('option_value_2', 'text','Option value', array('attr' => array('list' => 'option_value', 'placeholder' => 'Value')));

		$priceForm = $this->get('form')
			->setName('price')
			->addOptions(array(
				'auto_initialize' => false,
		));

		foreach ($this->get('product.price.types') as $type) {
			$priceForm->add($type, 'text', $this->trans('ms.commerce.product.price-sans.'.strtolower($type)))
				->val()->optional();
		}

		$form->add($priceForm->getForm(), 'form');

		return $form;
	}

	public function unitProcess($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);
		$this->_units   = $this->_product->getUnits()->all();
		$units          = $this->_product->getUnits();
		$form           = $this->_getUnitForm();

		if ($form->isValid() && $data = $form->getFilteredData()) {
			foreach ($data as $unitID => $values) {
				// original unit
				$unit = clone $this->_units[$unitID];
				// unit to update
				$changedUnit = clone $unit;

				if ($values['delete']) {
					$this->get('product.unit.delete')->delete($unit);

					continue;
				}
				$changedUnit->sku         = $values['sku'];
				$changedUnit->weightGrams = (int) $values['weight'];
				$changedUnit->visible     = (int) (bool) $values['visible'];

				foreach ($values['price'] as $type => $value) {
					$changedUnit->price[$type]->setPrice('GBP', $value, $this->get('locale'));
				}

				foreach ($values['options'] as $type => $value) {
					$changedUnit->options[$type] = $value;
				}

				if ($changedUnit != $unit) {
					$changedUnit = $this->get('product.unit.edit')->save($changedUnit);
				}

				if ($changedUnit->price !== $unit->price) {
					$changedUnit = $this->get('product.unit.edit')->savePrices($changedUnit);
				}

			}
		}

		return $this->redirectToRoute('ms.commerce.product.edit.units', array('productID' => $this->_product->id));
	}

	public function addUnitProccess($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);
		$form = $this->_addNewUnitForm();

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$unit              = $this->get('product.unit');
			$unit->sku         = $data['sku'];
			$unit->weightGrams = $data['weight'];
			$unit->revisionID  = 1;
			$unit->product     = $this->_product;

			foreach ($data['price'] as $type => $value) {
				$unit->price[$type]->setPrice('GBP', $value, $this->get('locale'));
			}

			$unit->authorship->create(new DateTimeImmutable, $this->get('user.current'));
			$unit->setOption($data['option_name_1'], $data['option_value_1']);

			if (!empty($data['option_name_2']) && !empty($data['option_value_2'])) {
				$unit->setOption($data['option_name_2'], $data['option_value_2']);
			}

			$unit = $this->get('product.unit.create')->save($unit);
			$unit = $this->get('product.unit.create')->savePrices($unit);
		}

		return $this->redirectToRoute('ms.commerce.product.edit.units', array('productID' => $this->_product->id));

	}

	public function process($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);

		$form = $this->_getProductForm();
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
			$product->category                   = $data['category'];
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

	protected function _getProductForm()
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
				'category'                   	=> $this->_product->category,
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

		$form->add('category', 'text', $this->trans('ms.commerce.product.category'))
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
			->val()
				->maxLength(4)
				->optional();
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
			->val()
				->maxLength(255)
				->optional();

		$form->add('export_description', 'textarea', $this->trans('ms.commerce.product.export-description'))
			->val()->optional();
		$form->add('export_value', 'text', $this->trans('ms.commerce.product.export-value'))
			->val()->optional();
		$form->add('export_manufacture_country_id', 'choice', $this->trans('ms.commerce.product.export-manufacture-country'), array(
			'choices' => $this->get('country.list')->getAll(),
			'attr' => array('data-help-key' => 'ms.cms.attributes.access.help'),
		))->val()->optional();

		return $form;
	}
}