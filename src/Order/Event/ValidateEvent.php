<?php

namespace Message\Mothership\Commerce\Order\Event;

class ValidateEvent extends Event
{
	protected $_errors = array();

	public function addError($error)
	{
		$this->_errors[] = $error;
	}

	public function getErrors()
	{
		return $this->_errors;
	}

	public function hasErrors()
	{
		return !empty($this->_errors);
	}
}