<?php

namespace Message\Mothership\Commerce\Order\Event;

use Message\Mothership\Commerce\Order\Order;

use Message\Cog\Event\Event as BaseEvent;

class Event extends BaseEvent
{
	protected $_order;

	public function __construct(Order $order)
	{
		$this->setOrder($order);
	}

	public function getOrder()
	{
		return $this->_order;
	}

	public function setOrder(Order $order)
	{
		$this->_order = $order;
	}
}