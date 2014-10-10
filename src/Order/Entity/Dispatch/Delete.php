<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\ValueObject\Authorship;

use Message\Cog\DB;
use Message\Cog\DB\Result;
use Message\User\UserInterface;

/**
 * Decorator for deleting dispatches.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Delete implements DB\TransactionalInterface
{
	protected $_query;
	protected $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param DB\Query      $query       The database query instance to use
	 * @param UserInterface $currentUser The currently logged in user
	 */
	public function __construct(DB\Query $query, UserInterface $user)
	{
		$this->_query       = $query;
		$this->_currentUser = $user;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return Delete Returns $this for chainability
	 */
	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_query = $transaction;

		return $this;
	}

	/**
	 * Delete a dispatch by marking it as deleted in the database.
	 *
	 * @param  Dispatch $dispatch The dispatch to be deleted
	 *
	 * @return Dispatch           The dispatch that was deleted, with the "delete"
	 *                            authorship data set
	 */
	public function delete(Dispatch $dispatch)
	{
		$dispatch->authorship->delete(new DateTimeImmutable, $this->_currentUser->id);

		$this->_query->run('
			UPDATE
				order_dispatch
			SET
				deleted_at = :at?d,
				deleted_by = :by?in
			WHERE
				dispatch_id = :id?i
		', array(
			'at' => $dispatch->authorship->deletedAt(),
			'by' => $dispatch->authorship->deletedBy(),
			'id' => $dispatch->id,
		));

		return $dispatch;
	}
}