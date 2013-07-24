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
	protected $_statuses;
	protected $_itemStatuses;
	protected $_entities;

	public function __construct(DB\Query $query, User\Loader $userLoader,
		Status\Collection $statuses, Status\Collection $itemStatuses, array $entities)
	{
		$this->_query        = $query;
		$this->_userLoader   = $userLoader;
		$this->_statuses     = $statuses;
		$this->_itemStatuses = $itemStatuses;
		$this->_entities     = $entities;
	}

	public function getByID($id)
	{
		return $this->_load($id);
	}

	/**
	 * Get orders for items with a specific current status.
	 *
	 * At least one item in the order must have one of the given statuses as its
	 * most recent (current) status.
	 *
	 * @param  int|array $statuses Status code or array of status codes
	 *
	 * @return array[Order]        Array of orders
	 */
	public function getByCurrentItemStatus($statuses)
	{
		if (!is_array($statuses)) {
			$statuses = (array) $statuses;
		}

		foreach ($statuses as $code) {
			if (!$this->_itemStatuses->exists($code)) {
				throw new Exception(sprintf('Order item status code `%s` not defined.', $code));
			}
		}

		$result = $this->_query->run('
			SELECT
				order_item.order_id,
				status_code
			FROM
				order_item
			JOIN (
				SELECT
					*
				FROM
					order_item_status
				ORDER BY
					order_item_status.created_at DESC
			) AS statuses USING (item_id)
			GROUP BY
				item_id
			HAVING
				status_code IN (?ij)
		', array($statuses));

		return $this->_load($result->flatten(), true);
	}

	protected function _load($ids, $returnArray = false)
	{
		if (!is_array($ids)) {
			$ids = (array) $ids;
		}

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
				order_id = (?ij)
		', array($ids));

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

			$orders[$key]->status = $this->_statuses->get($row->status_code);
		}

		return $returnArray ? $orders : reset($orders);
	}
}