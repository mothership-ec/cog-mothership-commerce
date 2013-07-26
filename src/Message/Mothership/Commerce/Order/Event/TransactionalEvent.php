<?php

namespace Message\Mothership\Commerce\Order\Event;

use Message\Cog\DB;

/**
 * Event that has an instance of a database transaction, handy if you need to
 * pass a transaction around to event listeners to build up queries.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class TransactionalEvent extends Event implements DB\TransactionalInterface
{
	protected $_trans;

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_trans = $trans;
	}

	public function getTransaction()
	{
		return $this->_trans;
	}
}