<?php

namespace Message\Mothership\Commerce\Product\Stock;

use Message\Cog\Event\Event;

/**
 * Event for setting the overall status code for an order.
 */
class StockChangeEvent extends Event
{
	protected $_movement;

	public function __construct(\Movement\Movement $movement)
	{
		$this->_movement = $movement;
	}

	/**
	 * Get the movement of the event
	 *
	 * @return \Movement\Movement $movement
	 */
	public function getMovement()
	{
		return $this->_movement;
	}
}