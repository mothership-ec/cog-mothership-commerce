<?php

namespace Message\Mothership\Commerce\Product\Tax\Strategy;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;

class ExclusiveTaxStrategy implements TaxStrategyInterface
{
	/**
	 * {@inheritDocs}
	 * 
	 * @param  double  $price   The price
	 * @param  TaxRate $taxRate The tax rate to use
	 * @return double           The display price
	 */
	public function getDisplayPrice($price, TaxRate $taxRate)
	{
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException('Price must be numeric, ' . $price . ' given');
		}

		return $price;
	}
}