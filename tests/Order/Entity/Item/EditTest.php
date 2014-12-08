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
}