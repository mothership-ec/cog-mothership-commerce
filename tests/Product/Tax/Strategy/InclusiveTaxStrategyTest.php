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

		$this->_taxRate->shouldReceive('getTaxedPrice')
			->with($price)
			->zeroOrMoreTimes()
			->andReturn(120);


		$this->assertEquals(120, $strategy->getDisplayPrice($price, $this->_taxRate));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidPriceException()
	{
		$strategy = new InclusiveTaxStrategy;

		$strategy->getDisplayPrice('Not a string', $this->_taxRate);
	}
}