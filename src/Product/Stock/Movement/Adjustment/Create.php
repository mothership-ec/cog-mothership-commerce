<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement\Adjustment;

use Message\Mothership\Commerce\Product\Stock\Movement\Adjustment\Adjustment;

use Message\User\UserInterface;

use Message\Cog\DB;

/**
 * Stock movement adjustment creator.
 */
class Create implements DB\TransactionalInterface
{
	protected $_query;

	public function __construct(DB\Transaction $query)
	{
		$this->_query = $query;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function create(Adjustment $adjustment)
	{
		$result = $this->_query->add('
			INSERT INTO
				stock_movement_adjustment
			SET
				stock_movement_id 	= :movementID?i,
				unit_id  			= :unitID?i,
				location 			= :location?s,
				delta    			= :delta?i
				
			ON DUPLICATE KEY UPDATE
				delta = delta + :delta?i

		', array(
			'movementID' => $adjustment->movement->id,
			'unitID' 	 => $adjustment->unit->id,
			'location'   => $adjustment->location->name,
			'delta'   	 => $adjustment->delta,
		));

		return $adjustment;
	}
}