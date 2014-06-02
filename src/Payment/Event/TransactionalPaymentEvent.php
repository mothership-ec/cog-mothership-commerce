<?php

namespace Message\Mothership\Commerce\Payment\Event;

use Message\Mothership\Commerce\Payment\Payment;

use Message\Cog\Event\Event;
use Message\Cog\DB;

/**
 * Transactional event for `Payment`s.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class TransactionalPaymentEvent extends PaymentEvent implements DB\TransactionalInterface
{
	protected $_trans;

	/**
	 * {@inheritDoc}
	 */
	public function setTransaction(DB\Transaction $trans)
	{
		$this->_trans = $trans;
	}

	public function getTransaction()
	{
		return $this->_trans;
	}
}