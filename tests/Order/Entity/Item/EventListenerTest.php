<?php

namespace Message\Mothership\Commerce\Test\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Status\Status;
use Message\Mothership\Commerce\Order\Entity\Item;
use Mockery as m;

class EventListenerTest extends \PHPUnit_Framework_TestCase
{
	protected $_listener;
	protected $_defaultStatus;

	public function setUp()
	{
		$this->_defaultStatus = new Status(100, 'Something');
		$edit = m::mock('Message\Mothership\Commerce\Order\Entity\Item\Edit');
		$this->_listener      = new Item\EventListener($this->_defaultStatus, $edit);
	}

	public function testSetDefaultActualPriceWithEntityEvent()
	{
		$item = new Item\Item;
		$event = $this->getMockBuilder('\\Message\\Mothership\\Commerce\\Order\\Event\\EntityEvent', ['getEntity'])
			->disableOriginalConstructor()
			->getMock();

		$event
			->expects($this->any())
			->method('getEntity')
			->will($this->returnValue($item));

		$item->listPrice = 10.99;

		$this->_listener->setDefaultActualPrice($event);

		$this->assertSame($item->listPrice, $item->actualPrice);

		// Try with an actual price set
		$item->actualPrice = 40.99;

		$this->_listener->setDefaultActualPrice($event);

		$this->assertSame(40.99, $item->actualPrice);
	}

	public function testSetDefaultActualPriceWithOrderEvent()
	{
		$item1 = new Item\Item;
		$item2 = new Item\Item;
		$item3 = new Item\Item;

		$item1->listPrice = 10.49;

		$item2->listPrice = 99.99;
		$item2->actualPrice = 89.99;

		$item3->listPrice = 44.44;

		$items = [$item1, $item2, $item3];
		$itemCollection = $this->getMock('\\Message\\Mothership\\Commerce\\Order\\Entity\\Collection', ['all', 'getIterator']);
		$order = $this->getMockBuilder('\\Message\\Mothership\\Commerce\\Order\\Order')
			->disableOriginalConstructor()
			->getMock();
		$event = $this->getMockBuilder('\\Message\\Mothership\\Commerce\\Order\\Event\\Event', ['getOrder'])
			->disableOriginalConstructor()
			->getMock();

		$event
			->expects($this->any())
			->method('getOrder')
			->will($this->returnValue($order));

		$itemCollection
			->expects($this->any())
			->method('all')
			->will($this->returnValue($items));

		$itemCollection
			->expects($this->any())
			->method('getIterator')
			->will($this->returnValue(new \ArrayIterator($items)));

		$order->items = $itemCollection;

		$this->_listener->setDefaultActualPrice($event);

		foreach ($items as $i => $item) {
			switch ($i) {
				case 0:
					$this->assertSame(10.49, $item->listPrice);
					$this->assertSame(10.49, $item->actualPrice);
					break;
				case 1:
					$this->assertSame(99.99, $item->listPrice);
					$this->assertSame(89.99, $item->actualPrice);
					break;
				case 2:
					$this->assertSame(44.44, $item->listPrice);
					$this->assertSame(44.44, $item->actualPrice);
					break;
			}
		}
	}
}