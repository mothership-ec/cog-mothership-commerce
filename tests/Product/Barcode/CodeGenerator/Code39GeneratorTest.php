<?php

namespace Message\Mothership\Commerce\Test\Product\Barcode\CodeGenerator;

use Message\Mothership\Commerce\Product\Barcode\CodeGenerator\Code39Generator;
use Image_Barcode2 as ImageBarcode;

class Code39GeneratorTest extends \PHPUnit_Framework_TestCase
{
	private $_unit;

	/**
	 * @var Code39Generator
	 */
	private $_generator;

	public function setUp()
	{
		$this->_unit = $this->getMockBuilder('Message\\Mothership\\Commerce\\Product\\Unit\\Unit')
			->disableOriginalConstructor()
			->getMock()
		;

		$this->_generator = new Code39Generator;
	}

	public function testGetName()
	{
		$this->assertSame(ImageBarcode::BARCODE_CODE39, $this->_generator->getName());
	}

	public function testGetBarcodeType()
	{
		$this->assertSame(ImageBarcode::BARCODE_CODE39, $this->_generator->getBarcodeType());
	}

	public function testGenerateFromUnit()
	{
		$this->_unit->id = 20;
		$this->assertEquals($this->_unit->id, $this->_generator->generateFromUnit($this->_unit));
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testGenerateFromUnitNoUnitID()
	{
		$this->_generator->generateFromUnit($this->_unit);
	}
}