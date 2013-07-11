<?php

namespace Message\Mothership\Commerce\Order;

use Message\User;

use Message\Cog\DB;

use Message\Cog\ValueObject\Authorship;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Decorator for loading orders.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader
{
	protected $_query;
	protected $_eventDispatcher;
	protected $_userLoader;

	public function __construct(DB\Query $query, User\Loader $userLoader, array $entities)
	{
		$this->_query      = $query;
		$this->_userLoader = $userLoader;
		$this->_entities   = $entities;
	}

	public function getByID($id)
	{
		return $this->_load($id);
	}

	protected function _load($id, $returnArray = false)
	{
		$result = $this->_query->run('
			SELECT
				*,
				order_id         AS id,
				order_id         AS orderID,
				currency_id      AS currencyID,
				conversion_rate  AS conversionRate,
				product_net      AS productNet,
				product_discount AS productDiscount,
				product_tax      AS productTax,
				product_gross    AS productGross,
				total_net        AS totalNet,
				total_discount   AS totalDiscount,
				total_tax        AS totalTax,
				total_gross      AS totalGross
			FROM
				order_summary
			WHERE
				order_id = ?i
		', $id);

		if (0 === count($result)) {
			return $returnArray ? array() : false;
		}

		$orders = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Order', array($this->_entities));

		foreach ($result as $key => $row) {
			$orders[$key]->user = $this->_userLoader->getByID($row->user_id);

			$orders[$key]->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			if ($row->updated_at) {
				$orders[$key]->authorship->update(
					new DateTimeImmutable(date('c', $row->updated_at)),
					$row->updated_by
				);
			}
		}

		return $returnArray ? $orders : reset($orders);
	}
}