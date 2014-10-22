<?php

namespace Message\Mothership\Commerce\Form\Extension\Type;

use Symfony\Component\Form;

class CurrencySelect extends Form\AbstractType
{
	protected $_currencies;

	public function __construct(array $currencies)
	{
		$this->_currencies = $currencies;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(['choices' => $this->_currencies])
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