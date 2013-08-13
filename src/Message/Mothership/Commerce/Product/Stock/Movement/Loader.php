<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement;

use Message\Mothership\Commerce\Product\Stock;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Product;

use Message\Mothership\Commerce\Product\Stock\Movement\Adjustment\Loader;
use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * stock movement loader
 */
class Loader
{
	protected $_query;
	protected $_adjustmentLoader;

	public function __construct(DB\Query $query, Loader $adjustmentLoader)
	{
		$this->_query = $query;
		$this->_adjustmentLoader = $adjustmentLoader;
	}

	public function getById($id)
	{
		$return = $this->_load($id);

		if($return && !is_array($return)) {
			$this->_adjustmentLoader->setMovement($return);
			$return->adjustments = $this->_adjustmentLoader->getAll();
		}

		return $return;
	}

	public function getByUnit(Unit $unit)
	{
		$result = $this->_query->run('
			SELECT DISTINCT
				stock_movement_id
			FROM
				stock_movement_adjustment
			WHERE
				unit_id = ?i
		', $unit->id);

		$return = $this->_load($result->flatten(), true, 'Message\\Mothership\\Commerce\\Product\\Stock\\PartialMovement');

		if($return) {
			foreach($return AS $movement) {
				$this->_adjustmentLoader->setMovement($movement);
				$movement->adjustments = $this->_adjustmentLoader->getByUnit($unit);
			}
		}

		return $return;
	}

	public function getByLocation(\Stock\Location\Location $location)
	{
		$result = $this->_query->run('
			SELECT DISTINCT
				stock_movement_id
			FROM
				stock_movement_adjustment
			WHERE
				location = ?s
		', $location);

		$return = $this->_load($result->flatten(), true, 'Message\\Mothership\\Commerce\\Product\\Stock\\PartialMovement');

		if($return) {
			foreach($return AS $movement) {
				$this->_adjustmentLoader->setMovement($movement);
				$movement->adjustments = $this->_adjustmentLoader->getByLocation($location);
			}
		}

		return $return;
	}

	public function getByProduct(Product $product)
	{
		$result = $this->_query->run('
			SELECT DISTINCT
				adjustment.stock_movement_id
			FROM
				product_unit AS unit
			INNER JOIN
				stock_movement_adjustment AS adjustment
			USING
				(unit_id)
			WHERE
				unit.productId = ?i
		', $product->id);

		$return = $this->_load($result->flatten(), true, 'Message\\Mothership\\Commerce\\Product\\Stock\\PartialMovement');
		$return->setProduct($product);

		return $return;
	}

	protected function _load($ids, $alwaysReturnArray = false, $className = 'Message\\Mothership\\Commerce\\Product\\Stock\\Movement')
	{
		if (!is_array($ids)) {
			$ids = (array) $ids;
		}

		if (!$ids) {
			return $alwaysReturnArray ? array() : false;
		}

		$result = $this->_query->run('
			SELECT
				*,
				stock_movement_id AS id,
			FROM
				stock_movement
			WHERE
				id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$movements = $result->bindTo($className);
		$return    = array();

		foreach ($result as $key => $row) {
			$movements[$key]->authorship->create(new DateTimeImmutable(date('c', $row->createdAt)), $row->createdBy);
			$return[$row->id] = $movements[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}
}