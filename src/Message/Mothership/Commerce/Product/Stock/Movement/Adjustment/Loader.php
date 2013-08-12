<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement\Adjustment;

use Message\Mothership\Commerce\Product\Stock;
use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * stock movement loader
 */
class Loader
{
	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	public function getByMovement(Stock\Movement $movement)
	{
		if ($movement instanceof PartialMovement) {
			return $this->getByPartialMovement();
		}

		$result = $this->_query->run('
			SELECT
				adjustment_id AS id,
				stock_movement_id AS movementID,
				unit_id AS unitID
				location,
				delta
			FROM
				stock_movement_adjustment
			WHERE
				stock_movement_id = ?i
		', array($movement->id));

		$adjustments = $result->bindTo('Message\\Mothership\\Commerce\\Product\\Stock\\Movement\\Adjustment\\Adjustment');
		$return    = array();

		foreach ($result as $key => $row) {
			if ($unit) {
				$adjustments[$key]->unit = $unit;
			}
			else {
				// TODO: load the unit, put it in here. we need the order loader i guess
			}

			$return[$row->id] = $adjustments[$key];
		}

		return count($return) > 1 ? $return : reset($return);
	}

	public function getByPartialMovement(Stock\PartialMovement $movement)
	{
		// check for requirements, adjust the query to only get those adjustments
		$result = $this->_query->run('
			SELECT
				adjustment.adjustment_id AS id,
				adjustment.stock_movement_id AS movementID,
				adjustment.unit_id AS unitID,
				adjustment.location,
				adjustment.delta
			FROM
				stock_movement_adjustment AS adjustment
			WHERE
				adjustment.stock_movement_id = :movementID
			' . $movement->unit ? 'AND adjustment.unitID = :unitID' : '' . '
			' . $movement->location ?  'AND adjustment.loccation = :location' : '' . '
			' . $movement->product ? '
			AND
				adjustment.unitID IN (
					SELECT
						unit_id
					FROM
						product_unit
					WHERE
						product_id = :productID
				)' . '
		', array(
			'movementID' => $movement->id,
			'unitID' => $movement->unit->id,
			'location' => $movement->location,
			'productID' => $movement->product->id,
		));
	}
}