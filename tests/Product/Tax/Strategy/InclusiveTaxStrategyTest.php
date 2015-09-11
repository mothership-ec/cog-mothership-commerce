<?php

namespace Message\Mothership\Commerce\Test\Product\Tax\Strategy;

use Mockery as m;
use Message\Mothership\Commerce\Product\Tax\Strategy\InclusiveTaxStrategy;

class InclusiveTaxStrategyTest extends \PHPUnit_Framework_TestCase
{
	private $_taxRate;
	private $_strategy;

	public function setUp()
	{
		$this->_taxRate = m::mock('Message\Mothership\Commerce\Product\Tax\Rate\TaxRate')
			->shouldReceive('getTaxRate')
			->zeroOrMoreTimes()
			->andReturn('20')
			->getMock()
		;

		$resolver = m::mock('Message\Mothership\Commerce\Product\Tax\Resolver\TaxResolver');
		$address = m::mock('Message\Mothership\Commerce\Address\Address');
		$baseTaxes = m::mock('Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection');
		$baseTaxes->shouldReceive('getTotalTaxRate')
			->zeroOrMoreTimes()
			->andReturn(20.00)
		;

		$address->countryID = 'GB';
		$resolver->shouldReceive('getTaxRates')
			->zeroOrMoreTimes()
			->andReturn($baseTaxes)
		;

		$this->_strategy = new InclusiveTaxStrategy($resolver, $address);
	}

	public function testGetNetPrice()
	{
		$this->assertEquals(100, $this->_strategy->getNetPrice(120));
	}
	
	public function testGetGrossPrice()
	{
		$strategy = $this->_strategy;
		$price = 120;

		$this->assertEquals(120, $strategy->getGrossPrice(120, $this->_taxRate));
	}

	public function testGetGrossPriceWithMismatched()
	{
		$strategy = $this->_strategy;

		$newRate = m::mock('Message\Mothership\Commerce\Product\Tax\Rate\TaxRate')
			->shouldReceive('getTaxRate')
			->zeroOrMoreTimes()
			->andReturn(10.00)
			->getMock()
		;

		$this->assertEquals(110, $strategy->getGrossPrice(120, $newRate));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidPriceException()
	{
		$strategy = $this->_strategy;
		$strategy->getNetPrice('Not a string', $this->_taxRate);
	}
}