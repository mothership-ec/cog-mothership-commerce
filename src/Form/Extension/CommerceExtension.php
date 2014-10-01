<?php

namespace Message\Mothership\Commerce\Form\Extension;

use Symfony\Component\Form\AbstractExtension;
use Message\Cog\Localisation\Translator;

/**
 * Extension for Commerce-specific types
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class CommerceExtension extends AbstractExtension
{

	protected $_currencies;
	private $_translator;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $currencies, Translator $translator)
	{
		$this->_currencies = $currencies;
		$this->_translator = $translator;
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
		];
	}
}
