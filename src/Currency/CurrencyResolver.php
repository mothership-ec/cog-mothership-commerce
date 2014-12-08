<?php

namespace Message\Mothership\Commerce\Currency;

/**
 * Currency resolver class. This class resolves the currency to use when rendering
 * the site.
 */
class CurrencyResolver implements CurrencyResolverInterface
{
	private $_default;
	private $_val;

	public function __construct($default, $val = null)
	{
		$this->_default = $default;
		$this->_val = $val;
	}

	/**
	 * {@inheritDocs}
	 * @return string the currency code to use
	 */
	public function getCurrency()
	{
		if($this->_val === null) {
			return $this->_default;
		}

		return $this->_val;
	}
}