<?php

namespace Message\Mothership\Commerce\Product\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductPricing extends AbstractType
{
	protected $_currencies;
	protected $_taxRates;

	public function __construct(array $currencies, array $taxRates)
	{
		$this->_currencies = $currencies;
		$this->_taxRates   = $taxRates;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		foreach ($options['currencies'] as $currency) {
			$builder->add($currency, 'price_group', ['currency' => $currency]);
		}

		$builder->add('tax_rates', 'choice', [
			'choices' => $options['tax_rates'],
			'data'    => isset($options['tax_rate'])?$options['tax_rate']:null,
		]);
	}

	/**
	 * Sets the default currency and tax rate options, product is required
	 *
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired([
			'product',
		]);

		$resolver->setDefaults([
			'currencies' => $this->_currencies,
			'tax_rates'  => $this->_taxRates,
		]);
	}

	public function getName()
	{
		return 'product_pricing';
	}
}