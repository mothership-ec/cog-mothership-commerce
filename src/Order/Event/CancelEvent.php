<?php

namespace Message\Mothership\Commerce\Order\Event;

use Message\Mothership\Commerce\Order;

/**
 * Class CancelEvent
 * @package Message\Mothership\Commerce\Order\Event
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Event to allow other modules (e.g. E-commerce) to determine how to process a refund.
 */
class CancelEvent extends Event
{
	/**
	 * @var string
	 */
	private $_controllerReference;

	/**
	 * @var array
	 */
	private $_params = [];

	/**
	 * @var Order\CancellationRefund
	 */
	private $_refund;

	/**
	 * @param Order\Order $order
	 * @param Order\CancellationRefund $refund
	 */
	public function __construct(Order\Order $order, Order\CancellationRefund $refund)
	{
		parent::__construct($order);
		$this->_refund = $refund;
	}

	/**
	 * Set the reference for the controller that handles the refund
	 *
	 * @param $reference
	 */
	public function setControllerReference($reference)
	{
		if (!is_string($reference)) {
			throw new \InvalidArgumentException('Reference must be a string, ' .gettype($reference) . ' given');
		}

		$this->_controllerReference = $reference;
	}

	/**
	 * Get the reference for the controller that handles the refund
	 *
	 * @return string
	 */
	public function getControllerReference()
	{
		return $this->_controllerReference;
	}

	/**
	 * Set the parameters for the controller that handles the refund
	 *
	 * @param array $params
	 */
	public function setParams(array $params)
	{
		$this->_params = $params;
	}

	/**
	 * Get the parameters for the controller that handles the refund
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}

	/**
	 * Set the refund object
	 *
	 * @param Order\CancellationRefund $refund
	 */
	public function setRefund(Order\CancellationRefund $refund)
	{
		$this->_refund = $refund;
	}

	/**
	 * Get the refund object
	 *
	 * @return Order\CancellationRefund
	 */
	public function getRefund()
	{
		return $this->_refund;
	}
}