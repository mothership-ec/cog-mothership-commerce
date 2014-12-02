<?php

namespace Message\Mothership\Commerce\Product\Tax\Strategy;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection;
use Message\Mothership\Commerce\Address\Address;

class ExclusiveTaxStrategy extends AbstractStrategy
{
	/**
	 * {@inheritDocs}
	 */
	public function getNetPrice($price)
	{
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException('Price must be numeric, ' . $price . ' given');
		}

		return $price;
	}
	
	public function getDefaultStrategyAddress()
	{
		return new Address;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'exclusive';
	}
}