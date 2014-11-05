<?php

namespace Message\Mothership\Commerce\Product\Tax\Strategy;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection;

class ExclusiveTaxStrategy implements TaxStrategyInterface
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

		if ( ($taxRate instanceof TaxRateCollection) || ($taxRate instanceof TaxRate) ) {
			return $taxRate->getTaxedPrice($price);
		} else {
			throw new \InvalidArgumentException('taxRate must be either instance of TaxRate or TaxRateCollection');
		}
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'exclusive';
	}
}