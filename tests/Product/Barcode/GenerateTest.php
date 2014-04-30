<?php

namespace Message\Mothership\Commerce\Test\Product\Barcode;

use Message\Mothership\Commerce\Product\Barcode\Generate;

class GenerateTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Generate
	 */
	protected $_generate;

	protected $_file;
	protected $_query;
	protected $_result;
	protected $_barcode;
	protected $_image;
	protected $_fileResource;

	protected $_barcodeVal = 123;
	protected $_height = 60;
	protected $_width = 1;
	protected $_type = 'code39';
	protected $_fileExt = 'png';

	public function setUp()
	{
		$this->_file  = $this->getMockBuilder('\\Message\\Cog\\Filesystem\\File')
			->setMethods(['getPublicUrl'])
			->disableOriginalConstructor()
			->getMock();

		$this->_query = $this->getMockBuilder('\\Message\\Cog\\DB\\Query')
			->setMethods(['run'])
			->disableOriginalConstructor()
			->getMock();

		$this->_result = $this->getMockBuilder('\\Message\\Cog\\DB\\Result')
			->setMethods(['bindTo'])
			->disableOriginalConstructor()
			->getMock();

		$this->_barcode = $this->getMockBuilder('\\Message\\Mothership\\Commerce\\Product\\Barcode\\Barcode')
			->setMethods(['getBarcode'])
			->getMock();

		$this->_image  = $this->getMockBuilder('\\Message\\Mothership\\Commerce\\Product\\Barcode\\Image')
			->setMethods(['getImage', 'getFile', 'save', 'exists', 'getDirectory', 'getPath'])
			->getMock();

		$this->_fileResource = fopen(__DIR__ . '/dummy.png', 'r');

		$this->_generate = new Generate(
			$this->_query,
			$this->_image,
			$this->_height,
			$this->_width,
			$this->_fileExt,
			$this->_type
		);
	}

	public function testGetOneOfEachNewImage()
	{
		$this->_query
			->expects($this->once())
			->method('run')
			->will($this->returnValue($this->_result));

		$this->_result
			->expects($this->once())
			->method('bindTo')
			->will($this->returnValue([$this->_barcode]));

		$this->_barcode
			->expects($this->once())
			->method('getBarcode')
			->will($this->returnValue($this->_barcodeVal));

		$this->_image
			->expects($this->once())
			->method('exists')
			->will($this->returnValue(false));

		$this->_image
			->expects($this->once())
			->method('getImage')
			->will($this->returnValue($this->_fileResource));

		$this->_image
			->expects($this->once())
			->method('save')
			->will($this->returnValue(true));

		$this->_image
			->expects($this->once())
			->method('getFile')
			->will($this->returnValue($this->_file));

		$barcodes = $this->_generate->getOneOfEach();

		$this->assertTrue(is_array($barcodes));

		$barcode = array_shift($barcodes);

		$this->assertInstanceOf('\\Message\\Mothership\\Commerce\\Product\\Barcode\\Barcode', $barcode);
	}
	public function testGetOneOfEachExistingImage()
	{
		$this->_query
			->expects($this->once())
			->method('run')
			->will($this->returnValue($this->_result));

		$this->_result
			->expects($this->once())
			->method('bindTo')
			->will($this->returnValue([$this->_barcode]));

		$this->_barcode
			->expects($this->once())
			->method('getBarcode')
			->will($this->returnValue($this->_barcodeVal));

		$this->_image
			->expects($this->once())
			->method('exists')
			->will($this->returnValue(true));

		$this->_image
			->expects($this->never())
			->method('getImage')
			->will($this->returnValue($this->_fileResource));

		$this->_image
			->expects($this->never())
			->method('save')
			->will($this->returnValue(true));

		$this->_image
			->expects($this->once())
			->method('getFile')
			->will($this->returnValue($this->_file));

		$barcodes = $this->_generate->getOneOfEach();

		$this->assertTrue(is_array($barcodes));

		$barcode = array_shift($barcodes);

		$this->assertInstanceOf('\\Message\\Mothership\\Commerce\\Product\\Barcode\\Barcode', $barcode);
	}

	public function testGetHeight()
	{
		$this->assertSame($this->_height, $this->_generate->getHeight());
	}

	public function testSetHeightFromInt()
	{
		$height = 40;
		$this->_generate->setHeight($height);

		$this->assertSame($height, $this->_generate->getHeight());
	}

	public function testSetHeightFromNumericString()
	{
		$height = '40';
		$this->_generate->setHeight($height);

		$this->assertSame(40, $this->_generate->getHeight());
	}

	public function testSetHeightFromFloat()
	{
		$height = 40.1;
		$this->_generate->setHeight($height);

		$this->assertSame(40, $this->_generate->getHeight());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetHeightFromInvalidString()
	{
		$this->_generate->setHeight('forty');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetHeightInvalidType()
	{
		$this->_generate->setHeight(null);
	}

	public function testGetWidth()
	{
		$this->assertSame($this->_width, $this->_generate->getWidth());
	}

	public function testSetWidthFromInt()
	{
		$width = 40;
		$this->_generate->setWidth($width);

		$this->assertSame($width, $this->_generate->getWidth());
	}

	public function testSetWidthFromNumericString()
	{
		$width = '40';
		$this->_generate->setWidth($width);

		$this->assertSame(40, $this->_generate->getWidth());
	}

	public function testSetWidthFromFloat()
	{
		$width = 40.1;
		$this->_generate->setWidth($width);

		$this->assertSame(40, $this->_generate->getWidth());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetWidthFromInvalidString()
	{
		$this->_generate->setWidth('forty');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetWidthInvalidType()
	{
		$this->_generate->setWidth(null);
	}

	public function testGetFileExt()
	{
		$this->assertSame($this->_fileExt, $this->_generate->getFileExt());
	}

	public function testSetFileExtPng()
	{
		$generate = new Generate(
			$this->_query,
			$this->_image,
			$this->_height,
			$this->_width,
			'jpg',
			$this->_type
		);

		$ext = 'png';

		$generate->setFileExt($ext);
		$this->assertSame($ext, $generate->getFileExt());
	}

	public function testSetFileExtJpg()
	{
		$ext = 'jpg';
		$this->_generate->setFileExt($ext);
		$this->assertSame($ext, $this->_generate->getFileExt());
	}

	public function testSetFileExtJpeg()
	{
		$ext = 'jpeg';
		$this->_generate->setFileExt($ext);
		$this->assertSame($ext, $this->_generate->getFileExt());
	}

	public function testSetFileExtGif()
	{
		$ext = 'gif';
		$this->_generate->setFileExt($ext);
		$this->assertSame($ext, $this->_generate->getFileExt());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetFileExtNotSupported()
	{
		$this->_generate->setFileExt('bmp');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetFileExtInvalidType()
	{
		$this->_generate->setFileExt(new \stdClass);
	}

	public function testGetBarcodeType()
	{
		$this->assertSame($this->_type, $this->_generate->getBarcodeType());
	}

	public function testSetBarcodeType()
	{
		$type = 'abc';
		$this->_generate->setBarcodeType($type);
		$this->assertSame($type, $this->_generate->getBarcodeType());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetBarcodeTypeInvalidType()
	{
		$this->_generate->setBarcodeType(new \stdClass);
	}
}