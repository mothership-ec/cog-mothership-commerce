<?php

namespace Message\Mothership\Commerce\Product\Tax\Strategy;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection;

class InclusiveTaxStrategy implements TaxStrategyInterface
{
	/**
	 * {@inheritDocs}
	 */
	public function getDisplayPrice($price, $taxRate)
	{
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException('Price must be numeric, ' . $price . ' given');
		}

		if ($taxRate instanceof TaxRateCollection) {
			$tax = 0.000;
			foreach ($taxRate as $rate) {
				$tax += $rate->getTax($price);
			}
			$price += $tax;

			return $price;
		} else if ($taxRate instanceof TaxRate) {
			return $taxRate->getTaxedPrice($price);
		} else {
			throw new InvalidArgumentException('taxRate must be either instance of TaxRate or TaxRateCollection');
		}
	}
}