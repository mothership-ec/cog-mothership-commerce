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

		foreach ($units as $unit) {
			$unitForm = $builder->create($unit->id, 'form');

			$unitForm->add('sku', 'text', [
				'label' => false,
				'data'  => $unit->sku,
				'attr'  => ['placeholder' => 'ms.commerce.product.units.sku.placeholder'],
			]);

			// Options Headings
			$headings = [];

			foreach ($units as $unit) {
				foreach ($unit->options as $name => $value) {
					$headings[$name] = ucfirst($name);
				}
			}

			// Create options form
			$optionsForm = $builder->create('options', 'form');
			foreach($headings as $type => $displayName) {
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

			// Make the pricing form section
			$currencyCollection = $builder->create('currencies', 'form');
			foreach ($options['currencies'] as $currency) {
				foreach($unit->getPrices() as $type => $price) {
					$priceData[$type] = $price->getPrice($currency, $options['locale']);
				}

				$currencyCollection->add($currency, 'price_group', [
					'currency' => $currency,
					'label'    => $currency,
					'pricing'  => $priceData,
				]);
			}

			$unitForm->add($currencyCollection);

			$unitForm->add('weight', 'text', [
				'data' => $unit->weight,
				'attr' => [
					'data-help-key' => 'ms.commerce.product.details.weight-grams.help'
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