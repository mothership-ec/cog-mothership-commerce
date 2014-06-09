<?php

namespace Message\Mothership\Commerce\Refund\Event;

use Message\Mothership\Commerce\Refund\Refund;

use Message\Cog\DB;

/**
 * Transactional event for `Refund`s.
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