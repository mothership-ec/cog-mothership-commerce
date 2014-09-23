<?php

namespace Message\Mothership\Commerce\Order;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\DB\Result;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\ValueObject\Authorship;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Decorator for deleting orders.
 *
 * @author Eleanor Shakeshaft <eleanor@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Delete implements DB\TransactionalInterface
{
	protected $_trans;
	protected $_currentUser;
	protected $_eventDispatcher;

	protected $_transOverriden = false;

	/**
	 * Constructor.
	 *
	 * @param DB\Transaction      $trans           The database transaction to use
	 * @param DispatcherInterface $eventDispatcher
	 * @param UserInterface       $currentUser     The currently logged in user
	 */
	public function __construct(DB\Transaction $trans,
		DispatcherInterface $eventDispatcher, UserInterface $user)
	{
		$this->_trans           = $trans;
		$this->_currentUser     = $user;
		$this->_eventDispatcher = $eventDispatcher;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return Delete Returns $this for chainability
	 */
	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_trans = $transaction;
		$this->_transOverriden = true;

		return $this;
	}

	/**
	 * Delete an order by marking it as deleted in the database.
	 *
	 * @param  Order     $order The order to be deleted
	 *
	 * @return Order     The order that was deleted, with the "delete" authorship data set
	 */
	public function delete(Order $order)
	{
		$order->authorship->delete(new DateTimeImmutable, $this->_currentUser->id);

		$event = new Event\TransactionalEvent($order);
		$event->setTransaction($this->_trans);
		$order = $this->_eventDispatcher->dispatch(Events::DELETE_START, $event)
			->getOrder();

		$this->_trans->run('
			UPDATE
				order_summary
			SET
				deleted_at = :at?d,
				deleted_by = :by?in
			WHERE
				order_id = :id?i
		', array(
			'at' => $order->authorship->deletedAt(),
			'by' => $order->authorship->deletedBy(),
			'id' => $order->id,
		));

		$event = new Event\TransactionalEvent($order);
		$event->setTransaction($this->_trans);
		$order = $this->_eventDispatcher->dispatch(Events::DELETE_END, $event)
			->getOrder();

		return $order;
	}

}