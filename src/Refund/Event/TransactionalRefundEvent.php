<?php

namespace Message\Mothership\Commerce\Refund\Event;

use Message\Mothership\Commerce\Refund\Refund;

use Message\Cog\Event\Event;
use Message\Cog\DB;

/**
 * Transactional event for `Refund`s.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class TransactionalRefundEvent extends RefundEvent implements DB\TransactionalInterface
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