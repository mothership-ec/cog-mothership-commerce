<?php

namespace Message\Mothership\Commerce\Test\Product\Image;

use Message\Mothership\Commerce\Product\Image\Loader;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	private $_query;
	private $_fileLoader;

	public function setUp()
	{
		$this->_query      = $this->getMockBuilder('\\Message\\Cog\\DB\\Query')
			->disableOriginalConstructor()
			->getMock();

		$this->_fileLoader = $this->getMockBuilder('\\Message\\Mothership\\FileManager\\File\\Loader')
			->disableOriginalConstructor()
			->setMethods(['getByID'])
			->getMock();
	}

	public function testGetByID()
	{
		$result = $this->getMockBuilder('\\Message\\Cog\\DB\\Result')
			->disableOriginalConstructor()
			->getMock();

		$image = $this->getMockBuilder('\\ßMessage\\Mothership\\Commerce\\Test\\Product\\Image\\Image')
			->disableOriginalConstructor()
			->getMock();

		$this->_query
			->expects($this->any())
			->method('run')
			->will($this->returnValue($result));

		$result->expects($this->any())
			->method('flatten')
			->will($this->returnValue(['b90b033153546c187a5b8179c016ebd6']));

		$result->expects($this->any())
			->method('bindTo')
			->will($this->returnValue([ $image, ]));

		$result->expects($this->any())
			->method('numRows')
			->will($this->returnValue(1));

		$result->expects($this->any())
			->method('first')
			->will($this->returnValue([[
				'type'      => 'default',
				'productID' => '2',
				'fileID'    => 1410,
				'locale'    => 'en_GB',
				'createdAt' => '1394641204',
				'createdBy' => '1',
				'id'        => 'b90b033153546c187a5b8179c016ebd6',
				], ]));

		$loader = new Loader($this->_query, $this->_fileLoader);
		$image = $loader->getByID('b90b033153546c187a5b8179c016ebd6');

		$this->assertEquals($image->id, 'b90b033153546c187a5b8179c016ebd6');
		$this->assertEquals($image->type, 'default');
		$this->assertEquals($image->product, 2);
		$this->assertEquals($image->fileID, 1401);
		$this->assertEquals($image->options, [
				'colour' => 'blue',
				'other'  => 'something',
				]);
	}
}