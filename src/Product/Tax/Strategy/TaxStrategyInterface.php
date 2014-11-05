<?php

namespace Message\Mothership\Commerce\Product\Tax\Strategy;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;

/**
 * @author Samuel Trangmar-Keates sam@message.co.uk
 *
 * The prices coming into this class are the prices from the product. These 
 * should fit the strategy.
 */
interface TaxStrategyInterface
{
	/**
	 * Gets the net price based on a price and a tax rate
	 * 
	 * @param  double                    $price   The price
	 * @param  TaxRate|TaxRateCollection $taxRate The tax rate to use
	 * @return double                    The display price
	 */
	public function getNetPrice($price, $taxRate);

	/**
	 * Gets the price to display based on a price and a tax rate
	 * 
	 * @param  double                    $price   The price
	 * @param  TaxRate|TaxRateCollection $taxRate The tax rate to use
	 * @return double                    The display price
	 */
	public function getGrossPrice($price, $taxRate);
}