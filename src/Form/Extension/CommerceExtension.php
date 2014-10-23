<?php

namespace Message\Mothership\Commerce\Form\Extension;

use Symfony\Component\Form\AbstractExtension;

/**
 * Extension for Commerce-specific types
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class CommerceExtension extends AbstractExtension
{

	protected $_currencies;
	protected $_priceTypes;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $currencies, array $priceTypes)
	{
		$this->_currencies = $currencies;
		$this->_priceTypes = $priceTypes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function loadTypes()
	{
		return array(
			new Type\CurrencySetType($this->_currencies),
			new Type\CurrencySelect($this->_currencies),
			new Type\PriceGroup($this->_priceTypes),
		);
	}
}
