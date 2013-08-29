<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement;

use Message\Mothership\Commerce\Product\Stock\Movement\Movement;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Stock movement creator.
 */
class Create implements DB\TransactionalInterface
{
	protected $_currentUser;
	protected $_query;
	protected $_adjustmentCreator;

	public function __construct(DB\Transaction $query, UserInterface $currentUser, Adjustment\Create $adjustmentCreator)
	{
		$this->_query       	  = $query;
		$this->_currentUser 	  = $currentUser;
		$this->_adjustmentCreator = $adjustmentCreator;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
		$this->_adjustmentCreator->setTransaction($trans);
	}

	public function create(Movement $movement)
	{
		$movement = $this->createWithoutAdjustments($movement);

		foreach($movement->adjustments as $adjustment)
		{
			$adjustment = $this->_adjustmentCreator->create($adjustment);
		}

		return $movement;
	}

	public function createWithoutAdjustments(Movement $movement)
	{
		if(!$movement->reason) {
			throw new \IllegalArgumentException("Cannot save a movement without reason to the database!");
		}

		// Set create authorship data if not already set
		if (!$movement->authorship->createdAt()) {
			$movement->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$result = $this->_query->add('
			INSERT INTO
				stock_movement
			SET
				created_at  = :createdAt?d,
				created_by  = :createdBy?i,
				reason   	= :reason?s,
				note    	= :note?sn,
				automated	= :automated?b
		', array(
			'createdAt' => $movement->authorship->createdAt(),
			'createdBy' => $movement->authorship->createdBy(),
			'reason'  	=> $movement->reason->name,
			'note'   	=> $movement->note,
			'automated' => $movement->automated ? true : false,
		));

		$this->_query->setIDVariable('STOCK_MOVEMENT_ID');
		$movement->id = '@STOCK_MOVEMENT_ID';

		return $movement;
	}
}