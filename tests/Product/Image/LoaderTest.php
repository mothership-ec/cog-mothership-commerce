<?php

namespace Message\Mothership\Commerce\Test\Product\Image;

use Message\Mothership\Commerce\Product\Image\Loader;
use Mockery as m;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	private $_query;
	private $_fileLoader;
	private $_mockery;

	public function teardown()
    {
        m::close();
    }

	public function testGetByID()
	{
		$fileLoader = m::mock('\Message\Mothership\FileManager\File\Loader');
		$query  = m::mock('\Message\Mothership\Cog\DB\Query');
		$result = m::mock('\Message\Mothership\Cog\DB\Result');
		$image  = m::mock('\Message\Mothership\Commerce\Test\Product\Image\Image');

		$query->shouldReceive('run')->times(2)->andReturn($result);
		$result->shouldReceive('flatten')->once()->andReturn(['b90b033153546c187a5b8179c016ebd6']);

		$result->shouldReceive('bindTo')->once()->andReturn([ $image, ]);


		// $result->expects($this->any())
		// 	->method('first')
		// 	->will($this->returnValue([[
		// 		'type'      => 'default',
		// 		'productID' => '2',
		// 		'fileID'    => 1410,
		// 		'locale'    => 'en_GB',
		// 		'createdAt' => '1394641204',
		// 		'createdBy' => '1',
		// 		'id'        => 'b90b033153546c187a5b8179c016ebd6',
		// 		], ]));

		$loader = new Loader($query, $fileLoader);
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