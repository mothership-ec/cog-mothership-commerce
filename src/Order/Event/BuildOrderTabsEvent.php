<?php

namespace Message\Mothership\Commerce\Order\Event;

use Message\Mothership\Commerce\Order\Order;

use Message\Mothership\ControlPanel\Event\BuildMenuEvent as BaseEvent;

/**
 * Base event for the orders system. Allows an order to be set & get.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class BuildOrderTabsEvent extends BaseEvent
{
	protected $_order;

	/**
	 * Constructor.
	 *
	 * @param Order $order The order to live in this event
	 */
	public function __construct(Order $order)
	{
		$this->setOrder($order);
	}

	/**
	 * Get the order relating to this event.
	 *
	 * @return Order
	 */
	public function getOrder()
	{
		return $this->_order;
	}

	/**
	 * Set the order relating to this event.
	 *
	 * @param Order $order
	 */
	public function setOrder(Order $order)
	{
		$this->_order = $order;
	}
}