<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\User\UserInterface;
use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

use InvalidArgumentException;

/**
 * Transaction create decorator.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_query;
	protected $_loader;
	protected $_currentUser;
	protected $_transOverriden = false;

	// public function __construct(DB\Transaction $query, Loader $loader, UserInterface $currentUser)
	public function __construct(DB\Transaction $query, UserInterface $currentUser)
	{
		$this->_query       = $query;
		// $this->_loader      = $loader;
		$this->_currentUser = $currentUser;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
		$this->_transOverriden = true;

		return $this;
	}

	public function create(Transaction $transaction)
	{
		// Set create authorship data if not already set
		if (!$transaction->authorship->createdAt()) {
			$transaction->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$this->_validate($transaction);

		$this->_query->run('
			INSERT INTO
				transaction
			SET
				transaction_id = :transactionID?i,
				created_at     = :createdAt?d,
				created_by     = :createdBy?in,
				type           = :type?s,
				branch         = :branch?s,
				till           = :till?i
		', array(
			'transactionID' => $transaction->id,
			'createdAt'     => $transaction->authorship->createdAt(),
			'createdBy'     => $transaction->authorship->createdBy(),
			'type'          => $transaction->type,
			'branch'        => $transaction->branch,
			'till'          => $transaction->till,
		));

		$this->_query->setIDVariable('TRANSACTION_ID');
		$transaction->id = '@TRANSACTION_ID';

		// get ID here
		$this->_createRecords($transaction);

		// If the query was not in a transaction, return the re-loaded item
		if (!$this->_transOverriden) {
			$this->_query->commit();

			return $this->_loader->getByID($this->_query->getIDVariable('TRANSACTION_ID'));
		}

		return $transaction;
	}

	protected function _createRecords(Transaction $transaction)
	{
		foreach($transaction->records as $record)
		{
			$this->_query->run('
				INSERT INTO
					transaction_record
				SET
					transaction_id = :transactionID?i,
					record_id      = :recordID?i,
					type           = :type?s
			', array(
				'transactionID' => $transaction->id,
				'recordID'      => $record->getID(),
				'type'          => $record->getRecordType(),
			));
		}

	}

	protected function _validate(Transaction $transaction)
	{
		if (count($transaction->records) === 0) {
			throw new InvalidArgumentException('Could not create transaction: no records specified');
		}
	}
}