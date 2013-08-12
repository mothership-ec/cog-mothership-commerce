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

	/**
	 * {@inheritdoc}
	 */
	public function getByUnit(Unit $unit)
	{
		$result = $this->_query->run('
			SELECT
				adjustment_id
			FROM
				stock_movement_adjustment
			WHERE
				unit_id = ?i
		', $unit->id);

		return $this->_load($result->flatten(), true, $unit);
	}

	protected function _load($ids, $alwaysReturnArray = false, Order\Order $order = null)
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

		$addresses = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Entity\\Address\\Address');
		$return    = array();

		foreach ($result as $key => $row) {
			// Cast address lines to the correct structure
			for ($i = 1; $i <= 4; $i++) {
				$lineKey = 'line_' . $i;

				if ($row->{$lineKey}) {
					$addresses[$key]->lines[$i] = $row->{$lineKey};
				}
			}

			if ($order) {
				$addresses[$key]->order = $order;
			}
			else {
				// TODO: load the order, put it in here. we need the order loader i guess
			}

			$return[$row->id] = $addresses[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}

	protected function _loadAdjustements($ids)
	{
		if (!is_array($ids)) {
			$ids = (array) $ids;
		}
		if(!ids) {
			return false;
		}

		$result = $this->_query->run('
			SELECT
				*,
				adjustment_id AS adjustmentID
				stock_movement_id AS movementID,
				unit_id AS unitID,
			FROM
				order_address
			WHERE
				address_id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return false;
		}

		$addresses = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Entity\\Address\\Address');
		$return    = array();

		foreach ($result as $key => $row) {
			// Cast address lines to the correct structure
			for ($i = 1; $i <= 4; $i++) {
				$lineKey = 'line_' . $i;

				if ($row->{$lineKey}) {
					$addresses[$key]->lines[$i] = $row->{$lineKey};
				}
			}

			if ($order) {
				$addresses[$key]->order = $order;
			}
			else {
				// TODO: load the order, put it in here. we need the order loader i guess
			}

			$return[$row->id] = $addresses[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);

	}
}