<?php

namespace Message\Mothership\Commerce\Report\CommerceData;

use Message\Cog\DB\QueryBuilderFactory;

class ShippingData
{
	private $_builderFactory;

	/**
	 * Constructor.
	 *
	 * @param QueryBuilderFactory   $builderFactory
	 */
	public function __construct(QueryBuilderFactory $builderFactory)
	{
		$this->_builderFactory = $builderFactory;
	}

	/**
	 * Gets all SHIPPING data where:
	 * Order status is not CANCELLED (-300) or PAYMENT_PENDING (-100).
	 *
	 * All columns must match the other sub-queries used in SALES_REPORT.
	 * This because all subqueries are UNIONED together.
	 *
	 * @todo   Uncomment 'AND order_address.deleted_at IS NULL' when
	 *         deletable address functionality is merged.
	 *
	 * @return Query
	 */
	public function getQueryBuilder()
	{
		$data = $this->_builderFactory->getQueryBuilder();

		$data
			->select('order_summary.created_at AS "Date"')
			->select('order_summary.currency_id AS "Currency"')
			->select('IFNULL(net, 0) AS "Net"')
			->select('IFNULL(tax, 0) AS "Tax"')
			->select('IFNULL(gross, 0) AS "Gross"')
			->select('order_summary.type AS "Source"')
			->select('"Shipping" AS "Type"')
			->select('NULL AS "Item_ID"')
			->select('order_summary.order_id AS "Order_ID"')
			->select('NULL AS "Return_ID"')
			->select('NULL AS "Product_ID"')
			->select('display_name AS "Product"')
			->select('NULL AS "Option"')
			->select('country AS "Country"')
			->select('user.forename AS "User_Forename"')
			->select('user.surname AS "User_Surname"')
			->select('user.email AS "Email"')
			->select('order_summary.user_id AS "User_id"')
			->from('order_shipping')
			->join('order_summary', 'order_shipping.order_id = order_summary.order_id')
			->leftJoin('order_address', 'order_summary.order_id = order_address.order_id AND order_address.type = "delivery"') // AND order_address.deleted_at IS NULL
			->leftJoin('user', 'order_summary.user_id = user.user_id')
			->where('order_summary.status_code >= 0')
		;

		return $data;
	}
}