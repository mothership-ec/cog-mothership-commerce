<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\Cog\DB;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\User\UserInterface;

/**
 * Decorator to void a transaction.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Void implements DB\TransactionalInterface
{
	protected $_eventDispatcher;
	protected $_user;

	protected $_query;
	protected $_transOverridden = false;

	/**
	 * Constructor.
	 *
	 * @param DB\Transaction      $query           DB transaction
	 * @param DispatcherInterface $eventDispatcher Event dispatcher
	 * @param UserInterface       $user            The currently logged in user
	 */
	public function __construct(DB\Transaction $query, DispatcherInterface $eventDispatcher, UserInterface $user)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_user            = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_query           = $transaction;
		$this->_transOverridden = true;
	}

	/**
	 * Voids the given transaction.
	 *
	 * @param  Transaction $transaction Transaction to be voided
	 *
	 * @return Transaction              The voided transaction
	 *
	 * @throws \InvalidArgumentException If transaction is already voided
	 */
	public function void(Transaction $transaction)
	{
		if ($transaction->isVoided()) {
			throw new \InvalidArgumentException('Transaction has already been voided.');
		}

		$transaction->voidedAt = new DateTimeImmutable;
		$transaction->voidedBy = $this->_user->id;

		$result = $this->_query->run('
			UPDATE
				transaction
			SET
				voided_at = :at?d,
				voided_by = :by?in
			WHERE
				transaction_id = :id?i
		', [
			'at' => $transaction->voidedAt,
			'by' => $transaction->voidedBy,
			'id' => $transaction->id
		]);

		$event = new Event\TransactionalEvent($transaction);
		$event->setDbTransaction($this->_query);

		$transaction = $this->_eventDispatcher->dispatch(Events::VOID, $event)
			->getTransaction();

		if (!$this->_transOverridden) {
			$this->_query->commit();
		}

		return $transaction;
	}
}