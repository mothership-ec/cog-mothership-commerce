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
}