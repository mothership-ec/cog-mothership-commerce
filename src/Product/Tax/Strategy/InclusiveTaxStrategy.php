<?php

namespace Message\Mothership\Commerce\Product\Tax\Strategy;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection;

class InclusiveTaxStrategy implements TaxStrategyInterface
{
	/**
	 * {@inheritDocs}
	 */
	public function getNetPrice($price, $taxRate)
	{
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException('Price must be numeric, ' . $price . ' given');
		}

		return $price;
	}

	/**
	 * {@inheritDocs}
	 */
	public function getGrossPrice($price, $taxRate)
	{
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException('Price must be numeric, ' . $price . ' given');
		}

		$rate = 0.00;
		if ($taxRate instanceof TaxRateCollection) {
			$rate += $taxRate->getTotalTaxRate();

			return $price / (1 + $rate/100);
		} else if ($taxRate instanceof TaxRate) {
			$rate += $taxRate->getTaxRate();

			return $price / (1 + $rate/100);
		} else {
			throw new InvalidArgumentException('taxRate must be either instance of TaxRate or TaxRateCollection');
		}
	}
}