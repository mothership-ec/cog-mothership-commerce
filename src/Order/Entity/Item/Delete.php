<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\ValueObject\Authorship;

use Message\Cog\DB;
use Message\Cog\DB\Result;
use Message\User\UserInterface;

/**
 * Decorator for deleting items.
 *
 * @author Eleanor Shakeshaft <eleanor@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
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
	 * Delete a item by marking it as deleted in the database.
	 *
	 * @param  Item $item The item to be deleted
	 *
	 * @return Item       The item that was deleted, with the "delete"
	 *                    authorship data set
	 */
	public function delete(Item $item)
	{
		$item->authorship->delete(new DateTimeImmutable, $this->_currentUser->id);

		$this->_query->run('
			UPDATE
				order_item
			SET
				deleted_at = :at?d,
				deleted_by = :by?in
			WHERE
				item_id = :id?i
		', array(
			'at' => $item->authorship->deletedAt(),
			'by' => $item->authorship->deletedBy(),
			'id' => $item->id,
		));

		return $item;
	}
}