<?php

namespace Message\Mothership\Commerce\Order\Event;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Payable\PayableInterface;

class CancelEvent extends Event
{
	private $_controllerReference;
	private $_params = [];
	private $_payable;

	public function __construct(Order\Order $order, PayableInterface $payable)
	{
		parent::__construct($order);
		$this->_payable = $payable;
	}

	public function setControllerReference($reference)
	{
		if (!is_string($reference)) {
			throw new \InvalidArgumentException('Reference must be a string, ' .gettype($reference) . ' given');
		}

		$this->_controllerReference = $reference;
	}

	public function getControllerReference()
	{
		return $this->_controllerReference;
	}

	public function setParams(array $params)
	{
		$this->_params = $params;
	}

	public function getParams()
	{
		return $this->_params;
	}

	public function setPayable(PayableInterface $payable)
	{
		$this->_payable = $payable;
	}

	public function getPayable()
	{
		return $this->_payable;
	}
}