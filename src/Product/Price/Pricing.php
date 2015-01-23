<?php

namespace Message\Mothership\Commerce\Product\Price;

use  Message\Cog\Localisation\Locale;

class Pricing
{
	public $pricing = [];
	protected $_locale;

	public function __construct(Locale $locale)
	{
		$this->_locale = $locale;
		$this->pricing[$locale->getID()] = [];
	}

	public function setPrice($currencyID, $price, Locale $locale = null)
	{
		if (is_null($locale)) {
			$locale = $this->_locale;
		}

		if (!array_key_exists($locale->getID(), $this->pricing)) {
			$this->pricing[$locale->getID()] = [];
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

	public function hasPrice($currencyID, Locale $locale = null)
	{
		if ($locale === null) {
			$locale = $this->_locale;
		}

		return $this->pricing[$locale->getID()][$currencyID];
	}

	public function getCurrencies(Locale $locale = null)
	{
		if ($locale === null) {
			$locale = $this->_locale;
		}

		$currencies = [];

		if (!array_key_exists($locale->getID(), $this->pricing)) {
			throw new \LogicException(
				'`' . $locale->getID() . '` does not exist on pricing object, only the following: `' . implode(array_keys($this->pricing), '`, `') . '` are available'
			);
		}

		foreach ($this->pricing[$locale->getID()] as $key => $value) {
			$currencies[] = $key;
		}

		return $currrencies;
	}
}
