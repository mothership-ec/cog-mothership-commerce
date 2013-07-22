<?php

namespace Message\Mothership\Commerce\Product;

use  Message\Cog\Localisation\Locale;

class Pricing
{
	public $pricing = array();
	protected $_locale;

	public function __construct(Locale $locale)
	{
		$this->_locale = $locale;
	}

	public function setPrice($currencyID, $price, Locale $locale)
	{
		$this->pricing[is_null($locale) ? $this->_locale->getID() : $locale->getID()][$currencyID] = $price;
	}

	public function getPrice($currencyID, Locale $locale)
	{
		return isset($this->pricing[$locale->getID()][$currencyID]) ? $this->pricing[$locale->getID()][$currencyID] : 0;
	}

}
