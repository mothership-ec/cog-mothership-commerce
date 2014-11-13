<?php

namespace Message\Mothership\Commerce\Test\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Entity\Item\Item;
use Mockery as m;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection;

class ItemTest extends \PHPUnit_Framework_TestCase
{
	protected $_unit;
	protected $_product;
	protected $_taxRates;

	public function setUp()
	{
		$strategy = m::mock('Message\Mothership\Commerce\Product\Tax\Strategy\TaxStrategyInterface')
			->shouldReceive('getName')
			->zeroOrMoreTimes()
			->andReturn('inclusive')
			->getMock()
		;

		$t1 = m::mock('Message\Mothership\Commerce\Product\Tax\Rate\TaxRate')
			->shouldReceive('getRate')
			->zeroOrMoreTimes()
			->andReturn((float) 20)
			->shouldReceive('getName')
			->zeroOrMoreTimes()
			->andReturn('t1')
			->shouldReceive('getType')
			->zeroOrMoreTimes()
			->andReturn('VAT')
			->getMock()
		;

		$t2 = m::mock('Message\Mothership\Commerce\Product\Tax\Rate\TaxRate')
			->shouldReceive('getRate')
			->zeroOrMoreTimes()
			->andReturn((float) 10)
			->shouldReceive('getName')
			->zeroOrMoreTimes()
			->andReturn('t2')
			->shouldReceive('getType')
			->zeroOrMoreTimes()
			->andReturn('BAT')
			->getMock()
		;
		
		// not mocking because iterators suck
		$this->_taxRates = new TaxRateCollection;
		$this->_taxRates
			->add($t1)
			->add($t2)
		;

		$this->_product = m::mock('Message\Mothership\Commerce\Product\Product')
			->shouldReceive('getTaxRates')
			->zeroOrMoreTimes()
			->andReturn($this->_taxRates)
			->shouldReceive('getTaxStrategy')
			->zeroOrMoreTimes()
			->andReturn($strategy)
			->getMock()
		;
		
		$this->_unit    = m::mock('Message\Mothership\Commerce\Product\Unit\Unit')
			->shouldReceive('getProduct')
			->zeroOrMoreTimes()
			->andReturn($this->_product)
			->getMock()
		;

		$this->_unit->product = $this->_product;
	}

	public function testPopulateMatchingTaxRates()
	{
		$item = new Item;
		$item->populate($this->_unit);

		$expected = [];

		foreach ($this->_taxRates as $rate) {
			$expected[$rate->getType()] = $rate->getRate();
		}

		$this->assertEquals($expected, $item->getTaxRates());
	}

	public function testPopulateMatchingTaxStrategy()
	{
		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame('inclusive', $item->taxStrategy);
	}

	public function testSerializeUnserializeWithTax()
	{
		$item = new Item;
		$item->populate($this->_unit);

		$expected = [];

		foreach ($this->_taxRates as $rate) {
			$expected[$rate->getType()] = $rate->getRate();
		}

		$this->assertEquals($expected, $item->getTaxRates());
		$item = unserialize(serialize($item));
		$this->assertEquals($expected, $item->getTaxRates());
	}

	public function testPopulateMatchingProductID()
	{
		$var = 1337;
		$this->_unit->product->id = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame($var, $item->productID);
	}

	public function testPopulateMatchingProductName()
	{
		$var = 'test';
		$this->_unit->product->name = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame($var, $item->productName);
	}

	public function testPopulateMatchingUnitID()
	{
		$var = 1337;
		$this->_unit->id = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame($var, $item->unitID);
	}

	public function testPopulateMatchingUnitRevision()
	{
		$var = 1337;
		$this->_unit->revisionID = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame($var, $item->unitRevision);
	}

	public function testPopulateMatchingUnitSku()
	{
		$var = 'test';
		$this->_unit->sku = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame($var, $item->sku);
	}

	public function testPopulateMatchingUnitBarcode()
	{
		$var = 'test';
		$this->_unit->barcode = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame($var, $item->barcode);
	}

	public function testPopulateImplodedUnitOptions()
	{
		$var = [
			'unit',
			'tests',
			'are',
			'fun'
		];

		$this->_unit->options = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame(implode($var, ', '), $item->options);
	}

	public function testPopulateMatchingProductBrand()
	{
		$var = 'test';
		$this->_unit->product->brand = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame($var, $item->brand);
	}

	public function testPopulateMatchingWeight()
	{
		$var = 1337;
		$this->_unit->weight = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame($var, $item->weight);
	}

	public function testPopulateWeightIsIntFromString()
	{
		$this->_unit->weight = '1';

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertTrue(is_int($item->weight));
		$this->assertFalse(is_string($item->weight));
		$this->assertFalse(is_float($item->weight));
	}

	public function testPopulateWeightIsIntFromFloat()
	{
		$this->_unit->weight = (float) 4.59;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertTrue(is_int($item->weight));
		$this->assertFalse(is_string($item->weight));
		$this->assertFalse(is_float($item->weight));
	}

	public function testPopulateWeightIsIntFromNull()
	{
		$this->_unit->weight = NULL;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertTrue(is_int($item->weight));
		$this->assertFalse(is_string($item->weight));
		$this->assertFalse(is_float($item->weight));
	}

	public function testPopulateWeightIsIntFromInt()
	{
		$this->_unit->weight = 1337;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertTrue(is_int($item->weight));
		$this->assertFalse(is_string($item->weight));
		$this->assertFalse(is_float($item->weight));
	}

	public function testGetTax()
	{
		$item = new Item;
		$item->populate($this->_unit);
	}
}