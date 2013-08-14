<?php

namespace Message\Mothership\Commerce\Order\Event;

/**
 * Event for setting the overall status code for an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class SetOrderStatusEvent extends Event
{
	protected $_status;

	/**
	 * Get the status code currently set on this event.
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->_status;
	}

	/**
	 * Set the status code on this event to set on the order.
	 *
	 * @param int $code
	 */
	public function setStatus($code)
	{
		$this->_status = $code;
	}
}