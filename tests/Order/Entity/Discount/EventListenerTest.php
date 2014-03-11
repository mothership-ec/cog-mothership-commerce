<?php

namespace Message\Mothership\Commerce\Test\Order\Entity\Discount;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Entity\Discount\EventListener;

class EventListenerTest extends \PHPUnit_Framework_TestCase
{
	protected $_listener;

	public function setUp()
	{
		$this->_listener = new EventListener;
	}

	public function testCalculateItemDiscountsWithItemsWithNoListPrice()
	{
		$order = $this->getMockBuilder('\\Message\\Mothership\\Commerce\\Order\\Order')
			->disableOriginalConstructor()
			->getMock();
		$event = $this->getMockBuilder('\\Message\\Mothership\\Commerce\\Order\\Event\\Event', ['getOrder'])
			->disableOriginalConstructor()
			->getMock();
		$itemCollection = $this->getMock('\\Message\\Mothership\\Commerce\\Order\\Entity\\Collection', ['all', 'getIterator']);
		$discountCollection = $this->getMock('\\Message\\Mothership\\Commerce\\Order\\Entity\\Collection', ['all', 'getIterator']);

		$event
			->expects($this->any())
			->method('getOrder')
			->will($this->returnValue($order));

		$item = new Order\Entity\Item\Item;
		$item->listPrice = 0;

		$items = [clone $item, clone $item];

		$order->items = $itemCollection;

		$itemCollection
			->expects($this->any())
			->method('all')
			->will($this->returnValue($items));

		$itemCollection
			->expects($this->any())
			->method('getIterator')
			->will($this->returnValue(new \ArrayIterator($items)));

		$discount = new Order\Entity\Discount\Discount;
		$discount->amount = 100;
		$discounts = [$discount];

		$order->discounts = $discountCollection;

		$discountCollection
			->expects($this->any())
			->method('all')
			->will($this->returnValue($discounts));

		$discountCollection
			->expects($this->any())
			->method('getIterator')
			->will($this->returnValue(new \ArrayIterator($discounts)));

		$this->_listener->calculateItemDiscount($event);

		foreach ($order->items as $item) {
			$this->assertEquals(0, $item->discount);
		}
	}
}