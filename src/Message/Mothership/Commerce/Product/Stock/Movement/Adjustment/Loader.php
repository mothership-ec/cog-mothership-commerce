<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement\Adjustment;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit;
use Message\Mothership\Commerce\Product\Stock;
use Message\Mothership\Commerce\Product\Stock\Location\Location;
use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * stock movement loader
 */
class Loader
{
	protected $_query;
	protected $_unitLoader;
	protected $_movement;

	public function __construct(DB\Query $query, Unit\Loader $unitLoader)
	{
		$this->_query = $query;
		$this->_unitLoader = $unitLoader;
	}

	public function setMovement(Stock\Movement\Movement $movement)
	{
		$this->_movement = $movement;
	}

	public function getAll()
	{
		$this->_checkForMovement();

		$ids = $this->_query->run('
			SELECT
				adjustment_id AS id
			FROM
				stock_movement_adjustment
			WHERE
				stock_movement_id = ?i
		', array(
			$this->_movement->id,
		));

		return $this->_load($ids->flatten());
	}

	public function getByLocation(Location $location)
	{
		$this->_checkForMovement();

		$ids = $this->_query->run('
			SELECT
				adjustment_id AS id
			FROM
				stock_movement_adjustment
			WHERE
				stock_movement_id = ?i
			AND
				location = ?s
		', array(
			$this->_movement->id,
			$location->name,
		));

		return $this->_load($ids->flatten());
	}

	public function getByProduct(Product $product)
	{
		$this->_checkForMovement();

		$ids = $this->_query->run('
			SELECT
				adjustment.adjustment_id AS id
			FROM
				product_unit AS unit
			INNER JOIN
				stock_movement_adjustment AS adjustment
			USING
				(unit_id)
			WHERE
				stock_movement_id = ?i
			AND
				unit.product_id = ?i

		', array(
			$this->_movement->id,
			$product->id,
		));

		return $this->_load($ids->flatten());
	}

	public function getByUnit(Unit\Unit $unit)
	{
		$this->_checkForMovement();

		$ids = $this->_query->run('
			SELECT
				adjustment_id AS id
			FROM
				stock_movement_adjustment
			WHERE
				stock_movement_id = ?i
			AND
				unit_id = ?i
		', array(
			$this->_movement->id,
			$unit->id,
		));

		return $this->_load($ids->flatten(), $unit);
	}

	protected function _load($ids, Unit\Unit $unit = null)
	{
		if (!is_array($ids)) {
			$ids = (array) $ids;
		}

		if (!$ids) {
			return array();
		}

		$result = $this->_query->run('
			SELECT
				adjustment_id AS id,
				stock_movement_id,
				unit_id,
				location,
				delta
			FROM
				stock_movement_adjustment
			WHERE
				adjustment_id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return array();
		}

		$adjustments 	= $result->bindTo('Message\\Mothership\\Commerce\\Product\\Stock\\Movement\\Adjustment\\Adjustment');
		$return 		= array();

		foreach ($result as $key => $row) {
			// add unit to adjustments
			if($unit) {
				$adjustments[$key]->unit = $unit;
			} else {
				$adjustments[$key]->unit = $this->_unitLoader->getByID($row->unit_id);
			}
			$adjustments[$key]->movement = $this->_movement;

			$return[$row->id] = $adjustments[$key];
		}

		return $return;
	}

	protected function _checkForMovement()
	{
		if(!$this->_movement) {
			throw new \Exception('Adjustments can only be returned when a movement is set!');
		}
	}
}