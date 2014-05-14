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
			'multiple' => false,
			'expanded' => true,
			'choices'  => [
				// @todo Use translations
				'automatic' =>'For all units in stock in location',
				'manual'    => 'Manually'
			],
		]);

		$builder->add('location', 'choice', [
			'multiple' => false,
			'expanded' => false,
			'choices'  => $this->_getLocations(),
			'empty_value' => false,

		]);

		$builder = $this->_addVariantFields($builder);

		$builder->add('offset', 'number', [
			'label' => 'Offset',
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