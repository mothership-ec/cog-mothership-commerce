<?php

namespace Message\Mothership\Commerce\Order\Transaction\Event;

use Message\Mothership\Commerce\Order\Transaction\Transaction;
use Message\Cog\Event\Event as BaseEvent;

/**
 * Base event for the transaction system. Allows an transaction to be set & get.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Event extends BaseEvent
{
	/**
	 * Order Transaction
	 *
	 * @var Transaction
	 */
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
	 * @return Transaction transaction relating to this event
	 */
	public function getTransaction()
	{
		return $this->_transaction;
	}

	/**
	 * Set the transaction relating to this event.
	 *
	 * @param  Transaction $transaction transaction
	 *
	 * @return Event                    $this for chainability
	 */
	public function setTransaction(Transaction $transaction)
	{
		$this->_transaction = $transaction;

		return $this;
	}
}