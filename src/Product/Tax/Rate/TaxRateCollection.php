<?php

namespace Message\Mothership\Commerce\Product\Tax\Rate;

use Message\Cog\ValueObject\Collection;

/**
 * @author Samuel Trangmar-Keates sam@message.co.uk
 *
 * A collection for tax rates
 */
class TaxRateCollection extends Collection
{
	protected function _configure()
	{
		$this
			->setType('Message\Mothership\Commerce\Product\Tax\Rate\TaxRate')
			->setKey(function($item) { return $item->getName(); })
		;
	}

	public function getTaxedPrice($price)
	{
		return $price * (1 + $this->getTotalTaxRate()/100);
	}

	public function getTotalTaxRate()
	{
		$rawRate = 0.00;

		foreach($this as $rate) {
			$rawRate += $rate->getRate();
		}

		return $rawRate;
	}
}