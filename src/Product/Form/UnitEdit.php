<?php

namespace Message\Mothership\Commerce\Product\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Message\Mothership\Commerce\Product\OptionLoader;

class UnitEdit extends AbstractType
{
	protected $_currencies;
	protected $_optionLoader;

	public function __construct(array $currencies, OptionLoader $optionLoader)
	{
		$this->_currencies    = $currencies;
		$this->_optionLoader = $optionLoader;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$units   = $options['units'];
		// Options Headings
		$headings = [];

		foreach ($units as $unit) {
			foreach ($unit->options as $name => $value) {
				$headings[$name] = ucfirst($name);
			}
		}

		foreach ($units as $unit) {

			// If a unit has no options, it is broken so we should ignore it
			if (count($unit->options) <= 0) {
				continue;
			}

			$unitForm = $builder->create($unit->id, 'form');

			$unitForm->add('sku', 'text', [
				'label' => false,
				'data'  => $unit->sku,
				'attr'  => ['placeholder' => 'ms.commerce.product.units.sku.placeholder'],
			]);

			// Create options form
			$optionsForm = $builder->create('options', 'form');
			foreach ($headings as $type => $displayName) {
				$choices = [];
				foreach ($this->_optionLoader->getByName($type) as $choice) {
					$choice = trim($choice);
					$choices[$choice] = $choice;
				}

				$fieldName = preg_replace('/[^a-z0-9]/i', '_', $type);
				$optionsForm->add($fieldName, 'datalist', [
					'data'    => (!empty($unit->options[$type])) ? $unit->options[$type] : null,
					'choices' => $choices,
				]);
			}
			$unitForm->add($optionsForm);

			$unitForm->add('prices', 'price_form', [
				'priced_entity' => $unit
			]);

			$unitForm->add('weight', 'text', [
				'data' => $unit->weight !== $unit->getProduct()->weight ? $unit->weight : null,
				'attr' => [
					'data-help-key' => 'ms.commerce.product.details.weight-grams.help',
				],
			]);

			$unitForm->add('visible', 'checkbox', [
				'data' => $unit->visible,
				'attr' => [
					'data-help-key' => 'ms.commerce.product.units.visible.help'
				],
			]);

			$builder->add($unitForm);
		}
	}

	/**
	 * Sets the default currency and tax rate options, product is required
	 *
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired([
			'units',
		]);

		$resolver->setOptional([
			'locale',
		]);

		$resolver->setDefaults([
			'currencies' => $this->_currencies,
			'locale'     => null,
		]);
	}

	public function getName()
	{
		return 'unit_edit';
	}
}