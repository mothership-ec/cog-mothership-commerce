<?php

namespace Message\Mothership\Commerce\Test\Product\Barcode\CodeGenerator;

use Message\Mothership\Commerce\Product\Barcode\CodeGenerator\Ean13Generator;
use Image_Barcode2 as ImageBarcode;

class Ean13GeneratorTest extends \PHPUnit_Framework_TestCase
{
	private $_unit;

	/**
	 * @var Ean13Generator
	 */
	private $_generator;

	public function setUp()
	{
		$this->_unit = $this->getMockBuilder('Message\\Mothership\\Commerce\\Product\\Unit\\Unit')
			->disableOriginalConstructor()
			->getMock()
		;

		$this->_generator = new Ean13Generator;
	}

	public function testGetName()
	{
		$this->assertSame(ImageBarcode::BARCODE_EAN13, $this->_generator->getName());
	}

	public function testGetBarcodeType()
	{
		$this->assertSame(ImageBarcode::BARCODE_EAN13, $this->_generator->getBarcodeType());
	}

	public function testGenerateFromUnitLowID()
	{
		$this->_unit->id = 1;
		$this->assertEquals('5000000000012', $this->_generator->generateFromUnit($this->_unit));
		$this->_unit->id = 4;
		$this->assertEquals('5000000000043', $this->_generator->generateFromUnit($this->_unit));
		$this->_unit->id = 5;
		$this->assertEquals('5000000000050', $this->_generator->generateFromUnit($this->_unit));
	}

	public function testGenerateFromUnitHighID()
	{
		$this->_unit->id = 12345;
		$this->assertEquals('5000000123452', $this->_generator->generateFromUnit($this->_unit));
		$this->_unit->id = 999999999;
		$this->assertEquals('5009999999994', $this->_generator->generateFromUnit($this->_unit));
		$this->_unit->id = 200;
		$this->assertEquals('5000000002009', $this->_generator->generateFromUnit($this->_unit));
	}

	public function testGenerateFromUnitWithIntPrefixFromConstructor()
	{
		$generator = new Ean13Generator(471);
		$this->_unit->id = 5;
		$this->assertEquals('4710000000059', $generator->generateFromUnit($this->_unit));

		$this->_unit->id = 1234;
		$this->assertEquals('4710000012342', $generator->generateFromUnit($this->_unit));
	}

	public function testGenerateFromUnitWithStringPrefixFromConstructor()
	{
		$generator = new Ean13Generator('471');
		$this->_unit->id = 5;
		$this->assertEquals('4710000000059', $generator->generateFromUnit($this->_unit));

		$this->_unit->id = 1234;
		$this->assertEquals('4710000012342', $generator->generateFromUnit($this->_unit));
	}

	public function testGenerateFromUnitWithIntPrefixFromSetPrefixNumber()
	{
		$this->_generator->setPrefixNumber(471);
		$this->_unit->id = 5;
		$this->assertEquals('4710000000059', $this->_generator->generateFromUnit($this->_unit));

		$this->_unit->id = 1234;
		$this->assertEquals('4710000012342', $this->_generator->generateFromUnit($this->_unit));
	}

	public function testGenerateFromUnitWithStringPrefixFromSetPrefixNumber()
	{
		$this->_generator->setPrefixNumber('471');
		$this->_unit->id = 5;
		$this->assertEquals('4710000000059', $this->_generator->generateFromUnit($this->_unit));

		$this->_unit->id = 1234;
		$this->assertEquals('4710000012342', $this->_generator->generateFromUnit($this->_unit));
	}

	public function testGenerateFromUnitWithIntPaddingFromConstructor()
	{
		$generator = new Ean13Generator(50, 3);
		$this->_unit->id = 2;
		$this->assertEquals('5033333333328', $generator->generateFromUnit($this->_unit));

		$this->_unit->id = 1234;
		$this->assertEquals('5033333312347', $generator->generateFromUnit($this->_unit));
	}

	public function testGenerateFromUnitWithStringPaddingFromConstructor()
	{
		$generator = new Ean13Generator(50, '3');
		$this->_unit->id = 2;
		$this->assertEquals('5033333333328', $generator->generateFromUnit($this->_unit));

		$this->_unit->id = 1234;
		$this->assertEquals('5033333312347', $generator->generateFromUnit($this->_unit));
	}

	public function testGenerateFromUnitWithIntPaddingFromSetPaddingNumber()
	{
		$this->_generator->setPaddingNumber(3);
		$this->_unit->id = 2;
		$this->assertEquals('5033333333328', $this->_generator->generateFromUnit($this->_unit));

		$this->_unit->id = 1234;
		$this->assertEquals('5033333312347', $this->_generator->generateFromUnit($this->_unit));
	}

	public function testGenerateFromUnitWithStringPaddingFromSetPaddingNumber()
	{
		$this->_generator->setPaddingNumber('3');
		$this->_unit->id = 2;
		$this->assertEquals('5033333333328', $this->_generator->generateFromUnit($this->_unit));

		$this->_unit->id = 1234;
		$this->assertEquals('5033333312347', $this->_generator->generateFromUnit($this->_unit));
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testGenerateFromUnitNoUnitID()
	{
		$this->_generator->generateFromUnit($this->_unit);
	}

	/**
	 * @expectedException \Message\Mothership\Commerce\Product\Barcode\CodeGenerator\Exception\BarcodeGenerationException
	 */
	public function testGenerateFromUnitTooLong()
	{
		$this->_unit->id = 9999999999;
		$this->_generator->setPrefixNumber(888888);
		$this->_generator->generateFromUnit($this->_unit);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetPrefixNumberWithFloat()
	{
		$this->_generator->setPrefixNumber(1.2);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetPrefixNumberWithNonNumber()
	{
		$this->_generator->setPrefixNumber('fifty');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetPaddingNumberWithFloat()
	{
		$this->_generator->setPaddingNumber(1.2);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetPaddingNumberWithNonNumber()
	{
		$this->_generator->setPaddingNumber('fifty');
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSetPaddingNumberWithTooManyDigits()
	{
		$this->_generator->setPaddingNumber(50);
	}
}