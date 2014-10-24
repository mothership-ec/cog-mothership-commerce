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
		$product   = $options['product'];
		$priceData = [];

		$currencyCollection = $builder->create('currencies', 'form');
		foreach ($options['currencies'] as $currency) {
			foreach($product->getPrices() as $type => $price) {
				$priceData[$type] = $price->getPrice($currency, $options['locale']);
			}

			$currencyCollection->add($currency, 'price_group', [
				'currency' => $currency,
				'label'    => $currency,
				'pricing'  => $priceData,
			]);
		}

		$builder->add($currencyCollection);

		$builder->add('tax_rate', 'choice', [
			'choices' => $options['tax_rate'],
			'data'    => $product->taxRate,
		]);

		$builder->add('export_value', 'money', [
			'data' => $product->exportValue,
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
			'tax_rate',
		]);

		$resolver->setOptional([
			'locale',
		]);

		$resolver->setDefaults([
			'currencies' => $this->_currencies,
			'tax_rate'   => $this->_taxRates,
			'locale'     => null,
		]);
	}

	public function getName()
	{
		return 'product_pricing';
	}
}