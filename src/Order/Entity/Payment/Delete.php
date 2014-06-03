<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\Mothership\Commerce\Payment\Delete as BaseDelete;

use Message\Cog\DB;

/**
 * Decorator for deleting order payments.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Delete implements DB\TransactionalInterface
{
	protected $_paymentDelete;

	/**
	 * Constructor
	 *
	 * @param BaseDelete $paymentDelete Base payment delete decorator
	 */
	public function __construct(BaseDelete $paymentDelete)
	{
		$this->_paymentDelete = $paymentDelete;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_paymentDelete->setTransaction($transaction);

		return $this;
	}

	/**
	 * Delete an order payment.
	 *
	 * @see Message\Mothership\Commerce\Payment\Delete::delete
	 *
	 * @param  Payment $payment The payment to delete
	 *
	 * @return Payment          The deleted payment
	 */
	public function delete(Payment $payment)
	{
		$payment->payment = $this->_paymentDelete->delete($payment->payment);

		return $payment;
	}
}