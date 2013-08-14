<?php

namespace Message\Mothership\Commerce\Order\Entity\Address;

use Message\Mothership\Commerce\Product\Stock\Movement\Movement;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order address creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_currentUser;
	protected $_query;

	public function __construct(DB\Transaction $query, UserInterface $currentUser)
	{
		$this->_query       = $query;
		$this->_currentUser = $currentUser;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function create(Movement $movement)
	{
		// Set create authorship data if not already set
		if (!$movement->authorship->createdAt()) {
			$movement->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$this->_query->add('
			INSERT INTO
				stock_movement
			SET
				created_at  = :createdAt?d,
				created_by  = :createdBy?i,
				reason   	= :reason?s,
				note    	= :note?sn,
		', array(
			'createdAt' => $movement->authorship->createdAt(),
			'createdBy' => $movement->authorship->createdBy(),
			'reason'  	=> $movement->reason,
			'note'   	=> $movement->note,
		));

		return $movement;
	}
}