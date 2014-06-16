<?php

namespace Message\Mothership\Commerce\Test\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Entity\Item;
use Message\Mothership\Commerce\Order\Status\Status;

use Message\Cog\Test\Event\FauxDispatcher;

class EditTest extends \PHPUnit_Framework_TestCase
{
	protected $_trans;
	protected $_statuses;
	protected $_dispatcher;
	protected $_user;
	protected $_edit;

	public function setUp()
	{
		$this->_trans = $this->getMockBuilder('\\Message\\Cog\\DB\\Transaction')
			->disableOriginalConstructor()
			->getMock();

		$this->_statuses   = $this->getMock('\\Message\\Mothership\\Commerce\\Order\\Status\\Collection');
		$this->_user       = $this->getMock('\\Message\\User\\User');
		$this->_dispatcher = new FauxDispatcher;
		$this->_edit       = new Item\Edit($this->_trans, $this->_dispatcher, $this->_statuses, $this->_user);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage item status `100` does not exist
	 */
	public function testUpdateStatusThrowsExceptionOnUndefinedStatus()
	{
		$this->_edit->updateStatus(new Item\Item, 100);
	}

	public function testUpdateStatusIgnoresFalseyValues()
	{
		$this->markTestIncomplete('This test needs looking at, the return value has changed so we need another way to test this');
		$status = new Status(100, 'Something');

		$this->_statuses
			->expects($this->any())
			->method('exists')
			->with(100)
			->will($this->returnValue(true));

		$this->_statuses
			->expects($this->any())
			->method('get')
			->with(100)
			->will($this->returnValue($status));

		$item = new Item\Item;
		$item->status = $status;

		$items = [
			null,
			$item,
			false
		];

		$this->assertFalse($this->_edit->updateStatus($items, 100));

		$collection = new Item\Collection;

		$collection->append($item); // this method don't allow any falsey values anyways

		$this->assertFalse($this->_edit->updateStatus($collection, 100));
	}
}