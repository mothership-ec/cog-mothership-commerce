<?php

namespace Message\Mothership\Commerce\Refund\Event;

use Message\Mothership\Commerce\Refund\Refund;

use Message\Cog\Event\Event as BaseEvent;

/**
 * Event that has an instance of a `Refund`.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Event extends BaseEvent
{
	protected $_refund;

	/**
	 * Constructor.
	 *
	 * @param Refund $refund Refund for this event
	 */
	public function __construct(Refund $refund)
	{
		$this->setRefund($refund);
	}

	/**
	 * Set/overwrite the refund for this event.
	 *
	 * @param Refund $refund
	 */
	public function setRefund(Refund $refund)
	{
		$this->_refund = $refund;
	}

	/**
	 * Get the refund for this event.
	 *
	 * @return Refund
	 */
	public function getRefund()
	{
		return $this->_refund;
	}
}