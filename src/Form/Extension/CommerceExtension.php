<?php

namespace Message\Mothership\Commerce\Form\Extension;

use Symfony\Component\Form\AbstractExtension;
use Message\Cog\Localisation\Translator;
use Message\Mothership\Commerce\Product\Type\Collection as ProductTypeCollection;

/**
 * Extension for Commerce-specific types
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class CommerceExtension extends AbstractExtension
{

	protected $_currencies;
	private $_translator;
	private $_priceTypes;
	private $_productTypes;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $currencies, Translator $translator, array $prices, ProductTypeCollection $productTypes)
	{
		$this->_currencies = $currencies;
		$this->_translator = $translator;
		$this->_priceTypes = $prices;
		$this->_productTypes = $productTypes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function loadTypes()
	{
		return [
			new Type\CurrencySetType($this->_currencies),
			new Type\UnitType($this->_translator),
			new Type\VariantType,
			new Type\ProductType($this->_translator, $this->_productTypes),
			new Type\CurrencySelect($this->_currencies),
			new Type\PriceGroup($this->_priceTypes),
			new Type\PriceForm($this->_currencies),
			new Type\UnitChoice,
		];
	}
}
