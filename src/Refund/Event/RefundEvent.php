<?php

namespace Message\Mothership\Commerce\Refund\Event;

use Message\Mothership\Commerce\Refund\Refund;

use Message\Cog\Event\Event;

/**
 * Event that has an instance of a `Refund`.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class RefundEvent extends Event
{
	protected $_refund;

	public function __construct(Refund $refund)
	{
		$this->setRefund($refund);
	}

	public function setRefund(Refund $refund)
	{
		$this->_refund = $refund;
	}

	public function getRefund()
	{
		return $this->_refund;
	}
}