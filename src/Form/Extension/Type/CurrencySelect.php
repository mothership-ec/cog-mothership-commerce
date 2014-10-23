<?php

namespace Message\Mothership\Commerce\Form\Extension\Type;

use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CurrencySelect extends Form\AbstractType
{
	protected $_currencies;

	public function __construct(array $currencies)
	{
		$this->_currencies = $currencies;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(['choices' => array_combine($this->_currencies, $this->_currencies)]);
	}

	public function getParent()
	{
		return 'choice';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'currency_select';
	}
}