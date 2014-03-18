<?php

namespace Message\Mothership\Commerce\Test\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Entity\Item\Item;

class ItemTest extends \PHPUnit_Framework_TestCase
{
	protected $_unit;
	protected $_product;

	public function setUp()
	{
		$this->_unit    = $this->getMockBuilder('\\Message\\Mothership\\Commerce\\Product\\Unit\\Unit')
			->disableOriginalConstructor()
			->getMock();
		$this->_product = $this->getMockBuilder('\\Message\\Mothership\\Commerce\\Product\\Product')
			->disableOriginalConstructor()
			->getMock();

		$this->_unit->product = $this->_product;
	}

	public function testPopulateMatchingTaxRate()
	{
		$var = 1.4;
		$this->_unit->product->taxRate = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame($var, $item->productTaxRate);
	}

	public function testPopulateTaxRateIsFloatFromString()
	{
		$this->_unit->product->taxRate = '1';

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertTrue(is_float($item->productTaxRate));
		$this->assertFalse(is_string($item->productTaxRate));
		$this->assertFalse(is_int($item->productTaxRate));
	}

	public function testPopulateTaxRateIsFloatFromInt()
	{
		$this->_unit->product->taxRate = (int) 1;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertTrue(is_float($item->productTaxRate));
		$this->assertFalse(is_string($item->productTaxRate));
		$this->assertFalse(is_int($item->productTaxRate));
	}

	public function testPopulateTaxRateIsFloatFromNull()
	{
		$this->_unit->product->taxRate = NULL;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertTrue(is_float($item->productTaxRate));
		$this->assertFalse(is_string($item->productTaxRate));
		$this->assertFalse(is_int($item->productTaxRate));
	}

	public function testPopulateTaxRateIsFloatFromWholeNumberFloat()
	{
		$this->_unit->product->taxRate = (float) 1.0;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertTrue(is_float($item->productTaxRate));
		$this->assertFalse(is_string($item->productTaxRate));
		$this->assertFalse(is_int($item->productTaxRate));
	}

	public function testPopulateTaxRateIsFloatFromDecimalFloat()
	{
		$this->_unit->product->taxRate = (float) 1.4;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertTrue(is_float($item->productTaxRate));
		$this->assertFalse(is_string($item->productTaxRate));
		$this->assertFalse(is_int($item->productTaxRate));
	}

	public function testPopulateMatchingTaxStrategy()
	{
		$var = 'test';
		$this->_unit->product->taxStrategy = $var;

		$item = new Item;
		$item->populate($this->_unit);

		$this->assertSame($var, $item->taxStrategy);
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
}