<?php

namespace Message\Mothership\Commerce\Test\Product\Tax\Rate;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection;

class TaxRateCollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testGetProductTaxes()
	{
		$taxes = [
			new TaxRate(20, 'PST', 't1'),
			new TaxRate(30, 'PST', 't2'),
			new TaxRate(40, 'PST', 't3'),
		];

		$collection = new TaxRateCollection($taxes);

		$this->assertEquals(90, $collection->getTotalTaxRate());
	}
}