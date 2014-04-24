<?php

namespace Message\Mothership\Commerce\Test\Entity\Item;

use Message\Mothership\Commerce\Order\Entity\Item;
use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Specification\OrderCanBeCancelledSpecification;

class OrderCanBeCancelledSpecificationTest extends \PHPUnit_Framework_TestCase
{
	protected $_item;
	protected $_itemCanBeCancelledSpecification;

	public function setUp()
	{
		$this->_item = new Item\Item;
		$this->_itemCanBeCancelledSpecification = new Item\ItemCanBeCancelledSpecification;
	}

	public function testItemCanBeCancelled()
	{
		$this->_item->status = new Order\Status\Status(Order\Statuses::AWAITING_DISPATCH, 'status');
		$this->assertTrue($this->_itemCanBeCancelledSpecification->isSatisfiedBy($this->_item));

		$this->_item->status->code = Order\Statuses::PROCESSING;
		$this->assertTrue($this->_itemCanBeCancelledSpecification->isSatisfiedBy($this->_item));

		$this->_item->status->code = Order\Statuses::CANCELLED;
		$this->assertFalse($this->_itemCanBeCancelledSpecification->isSatisfiedBy($this->_item));

		$this->_item->status->code = Order\Statuses::PARTIALLY_DISPATCHED;
		$this->assertFalse($this->_itemCanBeCancelledSpecification->isSatisfiedBy($this->_item));

		$this->_item->status->code = Order\Statuses::RECEIVED;
		$this->assertFalse($this->_itemCanBeCancelledSpecification->isSatisfiedBy($this->_item));
	}
}