<?php

namespace Message\Mothership\Commerce\Order\Entity\Refund;

use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\ValueObject\Authorship;

use Message\Cog\DB;
use Message\Cog\DB\Result;
use Message\User\UserInterface;

/**
 * Decorator for deleting refunds.
 */
class Delete
{
	protected $_query;
	protected $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param DB\Query            $query          The database query instance to use
	 * @param UserInterface       $currentUser    The currently logged in user
	 */
	public function __construct(DB\Query $query, UserInterface $user)
	{
		$this->_query           = $query;
		$this->_currentUser     = $user;
	}

	/**
	 * Delete a refund by marking it as deleted in the database.
	 *
	 * @param  Refund     $refund The refund to be deleted
	 *
	 * @return Refund     The refund that was deleted, with the "delete" authorship data set
	 */
	public function delete(Refund $refund)
	{
		$refund->authorship->delete(new DateTimeImmutable, $this->_currentUser->id);

		$result = $this->_query->run('
			UPDATE
				order_refund
			SET
				deleted_at = :at?d,
				deleted_by = :by?in
			WHERE
				refund_id = :id?i
		', array(
			'at' => $refund->authorship->deletedAt(),
			'by' => $refund->authorship->deletedBy(),
			'id' => $refund->id,
		));

		return $refund;
	}

}