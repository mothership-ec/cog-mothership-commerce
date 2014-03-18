<?php

namespace Message\Mothership\Commerce\Test\Product\Image;

use Message\Mothership\Commerce\Product\Image\Image;

class ImageTest extends \PHPUnit_Framework_TestCase
{
	protected $_file;
	protected $_fileLoader;
	protected $_image;

	public function setUp()
	{
		$this->_file = $this->getMock('\\Message\\Mothership\\FileManager\\File\\File');

		$this->_fileLoader = $this->getMockBuilder('\\Message\\Mothership\\FileManager\\File\\Loader')
			->setMethods(array('getByID'))
			->disableOriginalConstructor()
			->getMock();

		$this->_fileLoader
			->expects($this->once())
			->method('getByID')
			->will($this->returnValue($this->_file));

		$this->_image = new Image;

		$this->_image->setFileLoader($this->_fileLoader);
		$this->_image->fileID = 5;
	}

	public function testFilePropertyReturnsFile()
	{
		// Call it twice to test the mock expectation above that the file is only loaded once
		$this->assertSame($this->_file, $this->_image->file);
		$this->assertSame($this->_file, $this->_image->file);

		$this->assertTrue(isset($this->_image->file));
	}
}