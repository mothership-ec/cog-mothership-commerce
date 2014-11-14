<?php

namespace Message\Mothership\Commerce\Test\Order\EventListener;

use Message\Mothership\Commerce\Order\EventListener\TotalsListener;
use \Mockery as m;
use Message\Mothership\Commerce\Product\Tax\Resolver\StdOTaxResolver as Resolver;
use Message\Mothership\Commerce\Address\Address;

class TotalsListenerTest extends \PHPUnit_Framework_TestCase
{
	private $_listener;
	private $_resolver;
	private $_event;
	private $_order;

	public function setUp()
	{
		$this->_resolver = m::mock('Message\Mothership\Commerce\Product\Tax\Resolver\StdOTaxResolver');
		$this->_listener = new TotalsListener($this->_resolver);
		$this->_event = m::mock('Message\Mothership\Commerce\Order\Event\Event');
		$this->_order = m::mock('Message\Mothership\Commerce\Order\Order');

		$address = m::mock('Message\Mothership\Commerce\Address\Address');

		$this->_order
			->shouldReceive('getAddress')
			->zeroOrMoreTimes()
			->andReturn($address)
		;
	}

	public function testShippingTaxUpdate()
	{
		$taxes = m::mock('Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection')
			->shouldReceive('getTotalTaxRate')
			->zeroOrMoreTimes()
			->andReturn(20.00)
			->getMock()
		;

		$this->_event
			->shouldReceive('getOrder')
			->once()
			->andReturn($this->_order)
		;

		$this->_resolver->shouldReceive('getTaxRates')
			->once()
			->with([Resolver::DEFAULT_SHIPPING_TAX, $this->_order->getAddress(Address::DELIVERY)])
			->andReturn($taxes)
		;

		$this->_listener->calculateShippingTax($this->_event);
	}
}