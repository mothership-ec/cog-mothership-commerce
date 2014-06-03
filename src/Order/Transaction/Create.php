<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\User\UserInterface;
use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\Event\DispatcherInterface;

use InvalidArgumentException;

/**
 * Transaction create decorator.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Create
{
	protected $_query;
	protected $_loader;
	protected $_currentUser;
	protected $_transOverridden = false;

	public function __construct(
		DB\Transaction $query,
		Loader $loader,
		DispatcherInterface $eventDispatcher,
		UserInterface $currentUser
	) {
		$this->_query           = $query;
		$this->_loader          = $loader;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_currentUser     = $currentUser;
	}

	/**
	 * Sets transaction and sets $_transOverridden to true
	 *
	 * @param DBTransaction $trans transaction
	 */
	public function setDbTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
		$this->_transOverridden = true;

		return $this;
	}

	/**
	 * Creates the transaction.
	 * If a DB\Transaction has been explicitly set, adds the Transaction to the
	 * DB\Transaction. Otherwise commits $_query.
	 *
	 * @param  Transaction $transaction transaction to be created
	 *
	 * @return Transaction              if transaction wasn't overridden
	 *                                  re-loaded $transaction, otherwise just
	 *                                  $transaction
	 */
	public function create(Transaction $transaction)
	{
		// Set create authorship data if not already set
		if (!$transaction->authorship->createdAt()) {
			$transaction->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$event = new Event\TransactionalEvent($transaction);
		$event->setDbTransaction($this->_query);

		$transaction = $this->_eventDispatcher->dispatch(Events::CREATE_START, $event)
			->getTransaction();

		$this->_validate($transaction);

		$this->_query->run('
			INSERT INTO
				transaction
			SET
				created_at     = :createdAt?d,
				created_by     = :createdBy?in,
				type           = :type?s
		', array(
			'createdAt'     => $transaction->authorship->createdAt(),
			'createdBy'     => $transaction->authorship->createdBy(),
			'type'          => $transaction->type,
		));

		$sqlVariable = 'TRANSACTION_ID_' . uniqid();

		$this->_query->setIDVariable($sqlVariable);
		$transaction->id = '@' . $sqlVariable;

		$this->_createRecords($transaction);
		$this->_createAttributes($transaction);

		$loader = $this->_loader;

		$this->_query->attachEvent(
			Events::CREATE_COMPLETE,
			function ($dbTrans) use ($loader, $sqlVariable) {
				return new Event\Event(
					$loader->getByID($dbTrans->getIDVariable($sqlVariable))
				);
			}
		);

		if (!$this->_transOverridden) {
			$this->_query->commit();
		}

		return $transaction;
	}

	/**
	 * Creates all records for $transaction
	 *
	 * @param  Transaction $transaction transaction
	 */
	protected function _createRecords(Transaction $transaction)
	{
		foreach ($transaction->records as $record) {
			$this->_query->run('
				INSERT INTO
					transaction_record
				SET
					transaction_id = :transactionID?i,
					record_id      = :recordID?i,
					type           = :type?s
			', array(
				'transactionID' => $transaction->id,
				'recordID'      => $record->getRecordID(),
				'type'          => $record->getRecordType(),
			));
		}

	}

	/**
	 * Creates all attributes for $transaction
	 *
	 * @param  Transaction $transaction transaction
	 */
	protected function _createAttributes(Transaction $transaction)
	{
		foreach ($transaction->attributes as $name => $val) {
			$this->_query->run('
				INSERT INTO
					transaction_attribute
				SET
					transaction_id = :transactionID?i,
					name           = :name?s,
					value          = :value?s
			', array(
				'transactionID' => $transaction->id,
				'name'          => $name,
				'value'         => $val,
			));
		}
	}

	/**
	 * Validates $transaction by checking whether it contains records.
	 *
	 * @param  Transaction $transaction transaction
	 */
	protected function _validate(Transaction $transaction)
	{
		if (count($transaction->records) === 0) {
			throw new InvalidArgumentException('Could not create transaction: no records specified');
		}
	}
}