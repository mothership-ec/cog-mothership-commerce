<?php

namespace Message\Mothership\Commerce\Payment\Event;

use Message\Mothership\Commerce\Payment\Payment;

use Message\Cog\Event\Event as BaseEvent;

/**
 * Event that has an instance of a `Payment`.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Event extends BaseEvent
{
	protected $_payment;

	/**
	 * Constructor.
	 *
	 * @param Payment $payment Payment for this event
	 */
	public function __construct(Payment $payment)
	{
		$this->setPayment($payment);
	}

	/**
	 * Set/overwrite the payment for this event.
	 *
	 * @param Payment $payment
	 */
	public function setPayment(Payment $payment)
	{
		$this->_payment = $payment;
	}

	/**
	 * Get the payment for this event.
	 *
	 * @return Payment
	 */
	public function getPayment()
	{
		return $this->_payment;
	}
}