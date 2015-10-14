<?php

namespace Message\Mothership\Commerce\Product\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Message\Mothership\Commerce\Product\OptionLoader;
use Message\Mothership\Commerce\Field\OptionType;
use Message\Mothership\Commerce\Constraint\Product\UnitHasOptions;

class UnitAdd extends AbstractType
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
		// Options Headings
		$headings = [];
		foreach ($this->_optionLoader->getAllOptionNames() as $name => $value) {
			$headings[$value] = ucfirst($value);
		}

		$builder->add('sku', 'text', [
			'attr' => [
				'list' 			=> 'option_value',
				'placeholder' 	=> 'ms.commerce.product.units.sku.placeholder',
				'data-help-key' => 'ms.commerce.product.image.option.units.sku.help',
			],
			'constraints' => [
				new NotBlank,
			],
		])
		->add('weight', 'number', [
			'attr' => [
				'data-help-key' => 'ms.commerce.product.details.weight-grams.help',
			],
			'constraints' => [
				new NotBlank,
			],
		]);

		$optionType = new OptionType($headings, [
			'attr' => [
				'data-help-key' => [
					'name'  => 'ms.commerce.product.units.option.name.help',
					'value' => 'ms.commerce.product.units.option.value.help',
				]
		]]);

		$optionType
			->setNameLabel('ms.commerce.product.units.option.name.label')
			->setValueLabel('ms.commerce.product.units.option.value.label');

		$builder->add('options', 'collection', [
				'type'         => $optionType,
				'label'        => 'Options',
				'allow_add'    => true,
				'allow_delete' => true,
				'constraints'  => [
					new UnitHasOptions
				]
			]
		);

		$builder->add('prices', 'price_form');
	}

	/**
	 * Sets the default currency and tax rate options, product is required
	 *
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
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
		return 'unit_add';
	}
}