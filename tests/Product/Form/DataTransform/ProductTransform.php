<?php

namespace Message\Mothership\Commerce\Test\Product\DataTransform;

use Mockery as m;
use Message\Mothership\Commerce\Product\Form\DataTransform\ProductTransform as Transformer;
use Message\Mothership\Commerce\Product\Product;

class ProductTransform extends \PHPUnit_Framework_TestCase
{
	protected $_transformer;

	public function setUp()
	{
		$locale = m::mock('\Message\Cog\Localisation\Locale');
		$locale->shouldReceive('getId')->zeroOrMoreTimes()->andReturn('en-GB');
		$this->_transformer = new Transformer($locale, ['retail', 'rrp']);
	}

	public function testTransform()
	{
		$prices = ['retail' => 10, 'rrp' => 10];

		$product = m::mock('\Message\Mothership\Commerce\Product\Product')
			->shouldReceive('getName')
			->once()
			->andReturn('Name')
			->getMock()
			->shouldReceive('getBrand')
			->once()
			->andReturn('Brand')
			->getMock()
			->shouldReceive('getCategory')
			->once()
			->andReturn('Category')
			->getMock()
			->shouldReceive('getShortDescription')
			->once()
			->andReturn('Short Description')
			->getMock()
			->shouldReceive('getPrices')
			->once()
			->andReturn($prices)
			->getMock()
			->shouldReceive('getPrice')
			->twice()
			->andReturn(10)
			->getMock()
		;

		$result = $this->_transformer->transform($product);

		$this->assertTrue(
			$result == [
				'name' => 'Name',
				'brand' => 'Brand',
				'category' => 'Category',
				'short_description' => 'Short Description',
				'retail_price' => 10,
				'rrp_price' => 10,
			]
		);
	}

	public function testReverseTransform()
	{
		$in = [
			'name' => 'Name',
			'brand' => 'Brand',
			'category' => 'Category',
			'short_description' => 'Short Description',
			'retail_price' => 10,
			'rrp_price' => 20,
		];

		$result = $this->_transformer->reverseTransform($in);

		$this->assertTrue($in['name'] == $result->getName());
		$this->assertTrue($in['brand'] == $result->getBrand());
		$this->assertTrue($in['category'] == $result->getCategory());
		$this->assertTrue($in['short_description'] == $result->getShortDescription());
		$this->assertTrue($in['retail_price'] == $result->getPrice('retail'));
		$this->assertTrue($in['rrp_price'] == $result->getPrice('rrp'));

	}
}