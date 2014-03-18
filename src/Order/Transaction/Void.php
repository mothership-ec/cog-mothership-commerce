<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\User\UserInterface;

/**
 * Class used for voiding a transaction
 */
class Void
{
	protected $_query;
	protected $_user;

	public function __construct(Query $query, UserInterface $user)
	{
		$this->_query 	= $query;
		$this->_user	= $user;
	}

	/**
	 * Voids the given transaction
	 *
	 * @param  Transaction               $transaction Transaction to be voided
	 * @throws \InvalidArgumentException If transaction is already voided
	 *
	 * @return Transaction                            $transaction
	 */
	public function void(Transaction $transaction)
	{
		if ($transaction->isVoided()) {
			throw new \InvalidArgumentException('Transaction has already been voided.');
		}

		$transaction->void(new DateTimeImmutable(), $this->_user->id);

		$result = $this->_query->run(
			'UPDATE
				transaction
			SET
				voided_at = ?d,
				voided_by = ?in
			WHERE
				transaction_id = ?i',
			array(
				$transaction->voidedAt,
				$transaction->voidedBy,
				$transaction->id
			)
		);

		return $transaction;
	}
}