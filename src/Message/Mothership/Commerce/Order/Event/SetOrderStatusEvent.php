<?php

namespace Message\Mothership\Commerce\Order\Event;

class SetOrderStatusEvent extends Event
{
	protected $_status;

	public function getStatus()
	{
		return $this->_status;
	}

	public function setStatus($code)
	{
		$this->_status = $code;
	}
}