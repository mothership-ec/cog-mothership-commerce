<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement\Adjustment;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit;
use Message\Mothership\Commerce\Product\Stock;
use Message\Mothership\Commerce\Product\Stock\Location;
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
	protected $_locationCollection;

	public function __construct(DB\Query $query, Unit\Loader $unitLoader, Location\Collection $locationCollection)
	{
		$this->_query = $query;
		$this->_locationCollection = $locationCollection;

		$this->_unitLoader = $unitLoader;
		$this->_unitLoader->includeInvisible(true);
		$this->_unitLoader->includeOutOfStock(true);
	}

	public function setMovement(Stock\Movement\Movement $movement)
	{
		$this->_movement = $movement;
	}

	public function getAll()
	{
		$this->_checkForMovement();

		$result = $this->_query->run('
			SELECT
				stock_movement_id,
				unit_id,
				location,
				delta
			FROM
				stock_movement_adjustment
			WHERE
				stock_movement_id = ?i
		', array(
			$this->_movement->id,
		));

		return $this->_processResult($result);
	}

	public function getByLocation(Location\Location $location)
	{
		$this->_checkForMovement();

		$result = $this->_query->run('
			SELECT
				stock_movement_id,
				unit_id,
				location,
				delta
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

		return $this->_processResult($result);
	}

	public function getByProduct(Product $product)
	{
		$this->_checkForMovement();

		$result = $this->_query->run('
			SELECT
				adjustment.stock_movement_id,
				adjustment.unit_id,
				adjustment.location,
				adjustment.delta
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

		return $this->_processResult($result);
	}

	public function getByUnit(Unit\Unit $unit)
	{
		$this->_checkForMovement();

		$result = $this->_query->run('
			SELECT
				stock_movement_id,
				unit_id,
				location,
				delta
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

		return $this->_processResult($result, $unit);
	}

	protected function _processResult($result, Unit\Unit $unit = null)
	{
		if (0 === count($result)) {
			return array();
		}

		$adjustments = $result->bindTo('Message\\Mothership\\Commerce\\Product\\Stock\\Movement\\Adjustment\\Adjustment');
		$return 	 = array();

		foreach ($result as $key => $row) {
			// add unit to adjustments
			if($unit) {
				$adjustments[$key]->unit = $unit;
			} else {
				$adjustments[$key]->unit = $this->_unitLoader->getByID($row->unit_id);
			}
			$adjustments[$key]->location = $this->_locationCollection->get($row->location);
			$adjustments[$key]->movement = $this->_movement;

			$return[] = $adjustments[$key];
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