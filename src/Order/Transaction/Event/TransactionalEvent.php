<?php

namespace Message\Mothership\Commerce\Order\Transaction\Event;

use Message\Cog\DB;

/**
 * Event that adds a database transaction to the transaction event.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class TransactionalEvent extends Event
{

	/**
	 * Database transaction
	 *
	 * @var DB\Transaction
	 */
	protected $_dbTransaction;

	/**
	 * Gets the database transaction
	 *
	 * @return DB\Transaction database transaction
	 */
	public function getDbTransaction()
	{
		return $this->_dbTransaction;
	}

	/**
	 * Sets the database transaction
	 *
	 * @param  DB\Transaction $dbTransaction Database transaction
	 *
	 * @return Event                         $this for chainability
	 */
	public function setDbTransaction(DB\Transaction $dbTransaction)
	{
		$this->_dbTransaction = $dbTransaction;

		return $this;
	}
}