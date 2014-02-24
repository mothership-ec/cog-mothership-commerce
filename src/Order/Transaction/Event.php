<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\Cog\Event\Event as BaseEvent;

/**
 * Base event for the transaction system. Allows an transaction to be set & get.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Event extends BaseEvent
{
	protected $_transaction;

	/**
	 * Constructor.
	 *
	 * @param Transaction $transaction The transaction to live in this event
	 */
	public function __construct(Transaction $transaction)
	{
		$this->setTransaction($transaction);
	}

	/**
	 * Get the transaction relating to this event.
	 *
	 * @return Order
	 */
	public function getTransaction()
	{
		return $this->_transaction;
	}

	/**
	 * Set the transaction relating to this event.
	 *
	 * @param Transaction $transaction
	 */
	public function setTransaction(Transaction $transaction)
	{
		$this->_transaction = $transaction;
	}
}