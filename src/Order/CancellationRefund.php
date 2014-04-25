<?php

namespace Message\Mothership\Commerce\Order;

use Message\Cog\Service\Container;
use Message\Cog\ValueObject\Authorship;
use Message\Mothership\Commerce\Payable\PayableInterface;

/**
 * Cancellation Refund class, representing a Payable for the cancellation of
 * orders or order items.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class CancellationRefund implements PayableInterface
{
	protected $_amount;
	protected $_order;

	public function __construct(Order $order)
	{
		$this->_order = $order;

		return $this;
	}

	/**
	 * Sets order
	 * 
	 * @param Order $order order
	 */
	public function setOrder(Order $order)
	{
		$this->_order = $order;
	}

	/**
	 * Sets payable amount
	 * 
	 * @param float $amount payable amount
	 */
	public function setPayableAmount($amount)
	{
		$this->_amount = (float) $amount;
	}

	/**
	 * Gets order for cancellation refund
	 * @return Order order
	 */
	public function getOrder()
	{
		return $this->_order;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPayableAmount()
	{
		if (null === $this->_amount) {
			return $this->_order->getPayableTotal();
		}

		return $this->_amount;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPayableCurrency()
	{
		return $this->_order->getPayableCurrency();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPayableAddress($type)
	{
		return $this->_order->getPayableAddress($type);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPayableTransactionID()
	{
		return 'CANCELLATION-' . spl_object_hash($this);
	}
}