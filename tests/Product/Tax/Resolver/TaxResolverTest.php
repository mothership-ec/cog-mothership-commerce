<?php

namespace Message\Mothership\Commerce\Test\Product\Tax\Resolver; 

use Message\Mothership\Commerce\Product\Tax\Resolver\TaxResolver;
use Message\Mothership\Commerce\Product\Tax\Resolver\StdOTaxResolver as Resolver;
use Mockery as m;
use Message\Cog\Config\Compiler;

class TaxResolverTest extends \PHPUnit_Framework_TestCase
{
	private $compiler;
	private $productType;
	private $address;

	public function setUp()
	{
		$this->compiler      = new Compiler();
		$this->productType = m::mock('Message\Mothership\Commerce\Product\Type\ProductTypeInterface');
		$this->address     = m::mock('Message\Mothership\Commerce\Address\Address');
	}
	
	public function testCreate()
	{
		$this->compiler->add(file_get_contents(__DIR__ . '/../cfg/tax-1.yml'));
		$data = $this->compiler->compile();

		$this->productType->shouldReceive("getName")
			->zeroOrMoreTimes()
			->andReturn('basic')
		;

		$this->address->countryID = 'CA';

		$resolver = new Resolver($data->rates);
		$rates = $resolver->getProductTaxRates($this->productType, $this->address);

		// no default taxes
		$this->assertEquals([], $rates->all());
	}

	public function testVAT()
	{
		$this->compiler->add(file_get_contents(__DIR__ . '/../cfg/tax-1.yml'));
		$data = $this->compiler->compile();

		$this->productType->shouldReceive("getName")
			->zeroOrMoreTimes()
			->andReturn('basic')
		;

		$this->address->countryID = 'GB';

		$resolver = new Resolver($data->rates);
		$rates = $resolver->getProductTaxRates($this->productType, $this->address);

		// Only VAT returned
		$this->assertEquals(1, $rates->count());
		$rate = $rates->get('gb.default.' . Resolver::DEFAULT_PRODUCT_TAX);
		$this->assertEquals('VAT', $rate->getType());
		$this->assertEquals(20, $rate->getRate());
	}

	/**
	 * @expectedException LogicException
	 */
	public function testInvalidCountry()
	{
		$this->compiler->add(file_get_contents(__DIR__ . '/../cfg/tax-1.yml'));
		$data = $this->compiler->compile();

		$this->productType->shouldReceive("getName")
			->zeroOrMoreTimes()
			->andReturn('basic')
		;

		$this->address->countryID = 'INVALID';

		$resolver = new Resolver($data->rates);
		$rates = $resolver->getProductTaxRates($this->productType, $this->address);
	}

	/**
	 * @expectedException LogicException
	 */
	public function testNoDefaultRegion()
	{
		$this->compiler->add(file_get_contents(__DIR__ . '/../cfg/tax-1.yml'));
		$data = $this->compiler->compile();

		$this->productType->shouldReceive("getName")
			->zeroOrMoreTimes()
			->andReturn('basic')
		;

		$this->address->countryID = 'US';

		$resolver = new Resolver($data->rates);
		$rates = $resolver->getProductTaxRates($this->productType, $this->address);
	}

	public function testNoTaxProduct()
	{
		$this->compiler->add(file_get_contents(__DIR__ . '/../cfg/tax-1.yml'));
		$data = $this->compiler->compile();

		$this->productType->shouldReceive("getName")
			->zeroOrMoreTimes()
			->andReturn('book')
		;

		$this->address->countryID = 'GB';

		$resolver = new Resolver($data->rates);
		$rates = $resolver->getProductTaxRates($this->productType, $this->address);

		// Only VAT returned
		$this->assertEquals(0, $rates->count());
	}
}