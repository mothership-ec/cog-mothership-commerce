<?php

namespace Message\Mothership\Commerce\Currency;

interface CurrencyResolverInterface
{
	/**
	 * Get the currency string
	 * @return string The resolved curency
	 */
	public function getCurrency();
}