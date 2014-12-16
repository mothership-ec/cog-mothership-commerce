<?php

namespace Message\Mothership\Commerce\Product\Tax\Strategy;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection;

abstract class AbstractStrategy implements TaxStrategyInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getGrossPrice($price, $taxRate)
	{
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException('Price must be numeric, ' . $price . ' given');
		}

		if ($taxRate instanceof TaxRate) {
			$taxRate = $taxRate->getTaxRate();
		} elseif ($taxRate instanceof TaxRateCollection) {
			$taxRate = $taxRate->getTotalTaxRate();
		} else {
			throw new \InvalidArgumentException('Tax rate must be instance of TaxRate or TaxRateCollection');
		}

		$net = $this->getNetPrice($price);

		return $net + ($net * $taxRate/100);
	}
}