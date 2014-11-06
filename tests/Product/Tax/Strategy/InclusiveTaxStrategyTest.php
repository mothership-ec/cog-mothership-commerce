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

	public function testGetNetPrice()
	{
		$strategy = new InclusiveTaxStrategy;
		$price = 120;

		$this->_taxRate->shouldReceive('getTaxRate')
			->zeroOrMoreTimes()
			->andReturn(20);

		$this->assertEquals(100, $strategy->getNetPrice($price, $this->_taxRate));
	}
	
	public function testGetGrossPrice()
	{
		$strategy = new InclusiveTaxStrategy;
		$price = 120;

		$this->assertEquals(120, $strategy->getGrossPrice($price, $this->_taxRate));
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