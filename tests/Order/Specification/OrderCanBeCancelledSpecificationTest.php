<?php

namespace Message\Mothership\Commerce\Test\Order\Specification;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Specification\OrderCanBeCancelledSpecification;

class OrderCanBeCancelledSpecificationTest extends \PHPUnit_Framework_TestCase
{
	protected $_order;
	protected $_orderCanBeCancelledSpecification;

	public function setUp()
	{
		$this->_order = new Order\Order;
		$this->_orderCanBeCancelledSpecification = new OrderCanBeCancelledSpecification;
	}

	public function testOrderCanBeCancelled()
	{
		$this->_order->status = new Order\Status\Status(Order\Statuses::AWAITING_DISPATCH, 'status');
		$this->assertTrue($this->_orderCanBeCancelledSpecification->isSatisfiedBy($this->_order));

		$this->_order->status->code = Order\Statuses::PROCESSING;
		$this->assertTrue($this->_orderCanBeCancelledSpecification->isSatisfiedBy($this->_order));

		$this->_order->status->code = Order\Statuses::CANCELLED;
		$this->assertFalse($this->_orderCanBeCancelledSpecification->isSatisfiedBy($this->_order));

		$this->_order->status->code = Order\Statuses::PARTIALLY_RECEIVED;
		$this->assertFalse($this->_orderCanBeCancelledSpecification->isSatisfiedBy($this->_order));

		$this->_order->status->code = Order\Statuses::DISPATCHED;
		$this->assertFalse($this->_orderCanBeCancelledSpecification->isSatisfiedBy($this->_order));
	}
}