<?php

namespace Message\Mothership\Commerce\Order\Entity\Address;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order address loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader implements Order\Entity\LoaderInterface
{
	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByOrder(Order\Order $order)
	{
		$result = $this->_query->run('
			SELECT
				address_id
			FROM
				order_address
			WHERE
				order_id = ?i
		', $order->id);

		return $this->_load($result->flatten(), true, $order);
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
}