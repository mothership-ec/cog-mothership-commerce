<?php

namespace Message\Mothership\Commerce\Form\Extension\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Message\Mothership\Commerce\Product\Price\PricedInterface;

class PriceForm extends AbstractType
{
	protected $_currencies;

	public function __construct(array $currencies)
	{
		$this->_currencies = $currencies;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$entity   = $options['priced_entity'];
		if ($entity !== null && !$entity instanceof PricedInterface) {
			throw new \IllegalArgumentException('Option `priced_entity` must be instance of PricedInterface');
		}

		$priceData = [];

		$currencyCollection = $builder->create('currencies', 'form', ['label' => false,]);
		foreach ($options['currencies'] as $currency) {
			if ($entity) {	
				foreach($entity->getPrices() as $type => $price) {
					$priceData[$type] = $price?$price->getPrice($currency, $options['locale']):null;
				}
			}

			$currencyCollection->add($currency, 'price_group', [
				'currency' => $currency,
				'label'    => $currency,
				'pricing'  => $entity?$priceData:null,
			]);
		}

		$builder->add($currencyCollection);
	}

	/**
	 * Sets the default currency optins, product is required
	 *
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired([
			'priced_entity',
		]);

		$resolver->setOptional([
			'locale',
		]);

		$resolver->setDefaults([
			'currencies' => $this->_currencies,
			'locale'     => null,
			'priced_entity' => null,
		]);
	}

	public function getName()
	{
		return 'price_form';
	}
}