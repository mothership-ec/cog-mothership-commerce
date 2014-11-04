<?php

namespace Message\Mothership\Commerce\Product\Tax\Strategy;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;

interface TaxStrategyInterface
{
	/**
	 * Gets the price to display based on a price and a tax rate
	 * 
	 * @param  double                    $price   The price
	 * @param  TaxRate|TaxRateCollection $taxRate The tax rate to use
	 * @return double                    The display price
	 */
	public function getDisplayPrice($price, $taxRate);
}