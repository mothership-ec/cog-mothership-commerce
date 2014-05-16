<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

class Edit implements DB\TransactionalInterface
{
	protected $_query;
	protected $_transOverridden = false;

	protected $_currentUser;

	public function __construct(DB\Transaction $query, UserInterface $currentUser)
	{
		$this->_query           = $query;
		$this->_currentUser     = $currentUser;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query           = $trans;
		$this->_transOverridden = true;
	}

	public function save(Transaction $transaction)
	{
		$transaction->authorship->update(
			new DateTimeImmutable,
			$this->_currentUser->id
		);

		$this->_query->run('
			UPDATE
				transaction
			SET
				updated_at = :updatedAt?d,
				updated_by = :updatedBy?in
			WHERE
				transaction_id = :transactionID?i
		', [
			'updatedAt'     => $transaction->authorship->updatedAt(),
			'updatedBy'     => $transaction->authorship->updatedBy(),
			'transactionID' => $transaction->id,
		]);

		$this->_saveRecords($transaction);
		$this->_saveAttributes($transaction);

		if (!$this->_transOverridden) {
			$this->_query->commit();
		}

		return $transaction;
	}

	/**
	 * Deletes all records related to the transaction and saves the transaction's
	 * records to the db.
	 *
	 * @param  Transaction $transaction transaction
	 */
	protected function _saveRecords(Transaction $transaction)
	{
		$this->_query->run('
			DELETE FROM
				transaction_record
			WHERE
				transaction_id = :transactionID?i
		', [
			'transactionID' => $transaction->id,
		]);

		foreach ($transaction->records as $record)
		{
			$this->_query->run('
				INSERT INTO
					transaction_record
				SET
					transaction_id = :transactionID?i,
					record_id      = :recordID?i,
					type           = :type?s
			', [
				'transactionID' => $transaction->id,
				'recordID'      => $record->getRecordID(),
				'type'          => $record->getRecordType(),
			]);
		}

	}

	/**
	 * Deletes all attributes related to the transaction and saves the transaction's
	 * attributes to the db.
	 *
	 * @param  Transaction $transaction transaction
	 */
	protected function _saveAttributes(Transaction $transaction)
	{
		$this->_query->run('
			DELETE FROM
				transaction_attribute
			WHERE
				transaction_id = :transactionID?i
		', [
			'transactionID' => $transaction->id,
		]);

		foreach ($transaction->attributes as $name => $val) {
			$this->_query->run('
				INSERT INTO
					transaction_attribute
				SET
					transaction_id = :transactionID?i,
					name           = :name?s,
					value          = :value?s
			', [
				'transactionID' => $transaction->id,
				'name'          => $name,
				'value'         => $val,
			]);
		}
	}
}