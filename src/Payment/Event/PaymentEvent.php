<?php

namespace Message\Mothership\Commerce\Payment\Event;

use Message\Mothership\Commerce\Payment\Payment;

use Message\Cog\Event\Event;

/**
 * Event that has an instance of a `Payment`.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class PaymentEvent extends Event
{
	protected $_payment;

	public function __construct(Payment $payment)
	{
		$this->setPayment($payment);
	}

	public function setPayment(Payment $payment)
	{
		$this->_payment = $payment;
	}

	public function getPayment()
	{
		return $this->_payment;
	}
}