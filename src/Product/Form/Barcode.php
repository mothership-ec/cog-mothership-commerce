<?php

namespace Message\Mothership\Commerce\Product\Form;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Stock\Location\Collection as LocationCollection;

class Barcode extends Form\AbstractType
{
	protected $_units;
	protected $_locations;

	public function __construct(LocationCollection $locations)
	{
		$this->_locations = $locations;
	}

	public function setUnits(array $units)
	{
		$this->_units = $units;

		return $this;
	}

	public function getName()
	{
		return 'product_barcodes';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		if (!$this->_units) {
			throw new \LogicException('Units not set!');
		}

		$builder->add('type', 'choice', [
			'constraints' => [
				new Constraints\NotBlank,
			],
			'label'    => 'ms.commerce.product.barcode.type.label',
			'multiple' => false,
			'expanded' => true,
			'choices'  => [
				'automatic' => 'ms.commerce.product.barcode.type.automatic',
				'manual'    => 'ms.commerce.product.barcode.type.manual'
			],
			'data'     => 'automatic',
		]);

		$builder->add('location', 'choice', [
			'label'       => 'ms.commerce.product.barcode.location.label',
			'multiple'    => false,
			'expanded'    => false,
			'choices'     => $this->_getLocations(),
			'empty_value' => false,
			'attr'        => [
				'data-toggle-automatic' => '',
			],
			'label_attr'  => [
				'data-toggle-automatic' => '',
			],
		]);

		$builder = $this->_addVariantFields($builder);

		$builder->add('offset', 'number', [
			'label' => 'ms.commerce.product.barcode.offset.label',
			'attr'  => [
				'data-help-key' => 'ms.commerce.product.barcode.offset.help',
			]
		]);
	}

	protected function _addVariantFields(Form\FormBuilderInterface $builder)
	{
		foreach ($this->_units as $unit) {
			if (!$unit instanceof Unit) {
				throw new \InvalidArgumentException('Not unit');
			}
			$builder->add('unit_' . $unit->id, 'number', [
				'label' => $unit->getOptionString()
			]);
		}

		return $builder;
	}

	protected function _getLocations()
	{
		$locations = [];

		foreach ($this->_locations as $location) {
			$locations[$location->name] = $location->displayName;
		}

		return $locations;
	}
}