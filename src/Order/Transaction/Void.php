<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\User\UserInterface;

class Void
{
	protected $_query;
	protected $_user;

	public function __construct(Query $query, UserInterface $user)
	{
		$this->_query 	= $query;
		$this->_user	= $user;
	}

	public function void(Transaction $transaction)
	{
		$transaction->void(new DateTimeImmutable(), $this->_user->id);

		$result = $this->_query->run(
			'UPDATE
				transaction
			SET
				voided_at = ?d,
				voided_by = ?i
			WHERE
				transaction_id = ?i',
			array(
				$transaction->voidedAt,
				$transaction->voidedBy,
				$transaction->id
			)
		);

		return $result->affected() ? $transaction : false;
	}
}