<?php

namespace Message\Mothership\Commerce\Test\Product\Image;

use Message\Mothership\Commerce\Product\Image\Loader;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	private $_query;
	private $_fileloader;

	public function setUp()
	{
		$this->_query      = $this->getMockBuilder('\\Message\\Cog\\DB\\Query')
			->disableOriginalConstructor()
			->getMock();

		$this->_fileloader = $this->getMockBuilder('\\Message\\Mothership\\FileManager\\File\\Loader')
			->disableOriginalConstructor()
			->getMock();
	}

	public function testLoadByID()
	{
		$result = $this->getMockBuilder('\\Message\\Cog\\DB\\Result')
			->disableOriginalConstructor()
			->getMock();

		$this->_query
			->expects($this->any())
			->method('run')
			->will($this->returnValue($result));

		$result
			->expects($this->any())
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

		$loader = new Loader($_query, $_loader);
		$image = $loader->loadByID('b90b033153546c187a5b8179c016ebd6');

		$this->assertEquals($image->id, 'b90b033153546c187a5b8179c016ebd6');
		$this->assertEquals($image->type, 'default');
		$this->assertEquals($image->product, 2);
		$this->assertEquals($image->fileID, 1401);
		$this->assertEquals($image->options, [
				'colour' => 'blue',
				'other'  => 'something'
				]);
	}
}