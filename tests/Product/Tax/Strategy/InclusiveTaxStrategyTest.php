<?php

namespace Message\Mothership\Commerce\Test\Product\Tax\Strategy;

use Mockery as m;
use Message\Mothership\Commerce\Product\Tax\Strategy\InclusiveTaxStrategy;

class InclusiveTaxStrategyTest extends \PHPUnit_Framework_TestCase
{
	protected $_taxRate;

	public function setUp()
	{
		$this->_taxRate = m::mock('Message\Mothership\Commerce\Product\Tax\Rate\TaxRate');
	}

	public function testGetTaxedPrice()
	{
		$strategy = new InclusiveTaxStrategy;
		$price = 100;

		$this->assertEquals(100, $strategy->getNetPrice($price, $this->_taxRate));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidPriceException()
	{
		$strategy = new InclusiveTaxStrategy;

		$strategy->getNetPrice('Not a string', $this->_taxRate);
	}
}