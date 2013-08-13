<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement;

use Message\Mothership\Commerce\Product\Stock\Movement;
use Message\Mothership\Commerce\Product\Unit\Unit;

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

	public function getById($id)
	{
		return $this->_load($id);
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

		return $this->_load($result->flatten(), true, $unit);
	}

	protected function _load($ids, $alwaysReturnArray = false)
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
				address_id AS id,
				country_id AS countryID,
				state_id   AS stateID
			FROM
				order_address
			WHERE
				address_id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$movements = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Entity\\Address\\Address');
		$return    = array();

		foreach ($result as $key => $row) {
			$movements[$key]->authorship->create(new DateTimeImmutable(date('c', $row->createdAt)), $row->createdBy)

			$return[$row->id] = $movements[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}
}