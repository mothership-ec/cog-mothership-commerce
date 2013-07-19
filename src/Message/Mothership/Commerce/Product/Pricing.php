<?php

namespace Message\Mothership\Commerce\Product;

use  Message\Cog\Localisation\Locale;

class Pricing
{
	protected $pricing = array();
	protected $_locale;

	public function __construct(Locale $locale)
	{
		$this->_locale = $locale;
	}

	public function setPrice($currencyID, $price, Locale $locale = null)
	{
		$this->pricing[is_null($locale) ? $this->_locale->getID() : $locale][$currencyID] = $price;
	}

	public function getPrice($currencyID, $locale = null)
	{
		return $this->pricing[is_null($locale) ? $this->_locale->getID() : $locale][$currencyID];
	}

}
