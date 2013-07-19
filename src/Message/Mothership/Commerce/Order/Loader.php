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
				order_summary.*,
				order_summary.order_id         AS id,
				order_summary.order_id         AS orderID,
				order_summary.currency_id      AS currencyID,
				order_summary.conversion_rate  AS conversionRate,
				order_summary.product_net      AS productNet,
				order_summary.product_discount AS productDiscount,
				order_summary.product_tax      AS productTax,
				order_summary.product_gross    AS productGross,
				order_summary.total_net        AS totalNet,
				order_summary.total_discount   AS totalDiscount,
				order_summary.total_tax        AS totalTax,
				order_summary.total_gross      AS totalGross,
				order_shipping.name            AS shippingName,
				order_shipping.net             AS shippingNet,
				order_shipping.list_price      AS shippingListPrice,
				order_shipping.tax             AS shippingTax,
				order_shipping.tax_rate        AS shippingTaxRate,
				order_shipping.gross           AS shippingGross
			FROM
				order_summary
			LEFT JOIN
				order_shipping USING (order_id)
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