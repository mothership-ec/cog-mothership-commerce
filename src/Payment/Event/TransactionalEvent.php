<?php

namespace Message\Mothership\Commerce\Payment\Event;

use Message\Mothership\Commerce\Payment\Payment;

use Message\Cog\DB;

/**
 * Transactional event for `Payment`s.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class TransactionalEvent extends Event implements DB\TransactionalInterface
{
	protected $_trans;

	/**
	 * {@inheritDoc}
	 */
	public function setTransaction(DB\Transaction $trans)
	{
		$this->_trans = $trans;
	}

	/**
	 * Get the database transaction for this event.
	 *
	 * @return DB\Transaction
	 */
	public function getTransaction()
	{
		return $this->_trans;
	}
}