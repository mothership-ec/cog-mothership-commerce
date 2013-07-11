<?php

namespace Message\Mothership\Commerce\Order;

use Message\Cog\Event\Event as BaseEvent;

class Event extends BaseEvent
{
	const LOAD = 'commerce.order.load';

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