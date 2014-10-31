<?php

namespace Message\Mothership\Commerce\Test\Product\Tax\Rate;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;

class TaxRateTest extends \PHPUnit_Framework_TestCase
{
	public function testGetTax()
	{
		$rate = new TaxRate(20, 'def', 'PST');

		$this->assertEquals($rate->getTax(100), 20);
	}

	public function testGetTaxedPrice()
	{
		$rate = new TaxRate(20, 'def', 'PST');

		$this->assertEquals($rate->getTaxedPrice(100), 120);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidPriceInGetTax()
	{
		$rate = new TaxRate(20, 'def', 'PST');

		$rate->getTax('Not a string');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidPriceInGetTaxedPrice()
	{
		$rate = new TaxRate(20, 'def', 'PST');

		$rate->getTaxedPrice('Not a string');
	}
}