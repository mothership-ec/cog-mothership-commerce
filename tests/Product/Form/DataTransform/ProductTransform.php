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
		
		$location     = m::mock('\Message\Mothership\Commerce\Product\Stock\Location\Location');
		$productTypes = m::mock('\Message\Mothership\Commerce\Product\Type\Collection');
		$type         = m::mock('\Message\Mothership\Commerce\Product\Type\BasicProductType');
		$productTypes->shouldReceive('get')->once()->andReturn($type);

		$this->_transformer = new Transformer($locale, $location, ['retail', 'rrp'], $productTypes);
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
				'prices' => [
					'rrp' => 10,
					'retail' => 10,
				],
			]
		);
	}

	public function testReverseTransform()
	{
		$in = [
			'name' => 'Name',
			'brand' => 'Brand',
			'category' => 'Category',
			'type'  => 'basic',
			'short_description' => 'Short Description',
			'prices' => [
				'retail' => 10,
				'rrp' => 20,
			],
		];

		$result = $this->_transformer->reverseTransform($in);

		$this->assertTrue($in['name'] == $result->getName());
		$this->assertTrue($in['brand'] == $result->getBrand());
		$this->assertTrue($in['category'] == $result->getCategory());
		$this->assertTrue($in['short_description'] == $result->getShortDescription());
		$this->assertTrue($in['prices']['retail'] == $result->getPrice('retail'));
		$this->assertTrue($in['prices']['rrp'] == $result->getPrice('rrp'));

	}

	public function testReverseTransformWithUnits()
	{
		$in = [
			'name' => 'Name',
			'brand' => 'Brand',
			'category' => 'Category',
			'short_description' => 'Short Description',
			'type'  => 'basic',
			'prices' => [
				'retail' => 10,
				'rrp' => 20,
			],
			'units' => [
				[
					'sku'   => 'SKU1',
					'stock' => 10,
					'price' => 20,
					'variants' => [
						'colour' => ['key' => 'colour', 'value' => 'red'],
						'size'   => ['key' => 'size', 'value' => '5'],
					]
				],
				[
					'sku'   => 'SKU2',
					'stock' => 10,
					'price' => 20,
					'variants' => [
						'colour' => ['key' => 'colour', 'value' => 'blue'],
						'size'   => ['key' => 'size', 'value' => '6'],
					]
				],
			],
		];


		$result = $this->_transformer->reverseTransform($in);

		
		$units  = $result->getAllUnits();

		$this->assertTrue(sizeof($units) == 2);
		$this->assertTrue($units['SKU1']->getOption('colour') == 'red');
		$this->assertTrue($units['SKU2']->getOption('size') == 6);
	}
}