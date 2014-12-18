<?php

namespace Message\Mothership\Commerce\Product\Price;

use  Message\Cog\Localisation\Locale;

class Pricing
{
	public $pricing = array();
	protected $_locale;

	public function __construct(Locale $locale)
	{
		$this->_locale = $locale;
	}

	public function setPrice($currencyID, $price, Locale $locale = null)
	{
		if (is_null($locale)) {
			$locale = $this->_locale;
		}

		$this->pricing[$locale->getID()][$currencyID] = $price;

		return $this;
	}

	public function getPrice($currencyID, Locale $locale = null)
	{
		if ($locale === null) {
			$locale = $this->_locale;
		}

		return isset($this->pricing[$locale->getID()][$currencyID]) ? $this->pricing[$locale->getID()][$currencyID] : 0;
	}

	public function getCurrencies(Locale $locale = null)
	{
		if ($locale === null) {
			$locale = $this->_locale;
		}

		$currrencies = [];

		foreach ($this->pricing[$locale->getID()] as $key => $value) {
			$currencies[] = $key;
		}

		return $currencies;
	}
}
