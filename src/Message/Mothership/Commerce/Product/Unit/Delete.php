<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\User\User;

class Delete
{
	protected $_query;
	protected $_user;

	public function __construct(Query $query, User $user)
	{
		$this->_query 	= $query;
		$this->_user	= $user;
	}

	public function delete(Unit $unit)
	{
		$unit->authorship->delete(new DateTimeImmutable(), $this->_user->id);

		$result = $this->_query->run(
			'UPDATE
				product_unit
			SET
				deleted_at = ?d,
				deleted_by = ?i
			WHERE
				unit_id = ?i',
			array(
				$unit->authorship->deletedAt(),
				$unit->authorship->deletedBy(),
				$unit->id
			)
		);

		return $result->affected() ? $unit : false;
	}
}