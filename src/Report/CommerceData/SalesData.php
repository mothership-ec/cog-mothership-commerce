<?php

namespace Message\Mothership\Commerce\Report\CommerceData;

use Message\Cog\DB\QueryBuilderFactory;

class SalesData
{
	private $_builderFactory;

	public function __construct(QueryBuilderFactory $builderFactory)
	{
		$this->_builderFactory = $builderFactory;
	}

	public function getQueryBuilder()
	{
		$data = $this->_builderFactory->getQueryBuilder();

		$data
			->select('item.created_at AS "Date"')
			->select('order_summary.currency_id AS "Currency"')
			->select('IFNULL(item.net, 0) AS "Net"')
			->select('IFNULL(item.tax, 0) AS "Tax"')
			->select('IFNULL(item.gross, 0) AS "Gross"')
			->select('order_summary.type AS "Source"')
			->select('"Order" AS "Type"')
			->select('item.item_id AS "Item_ID"')
			->select('item.order_id AS "Order_ID"')
			->select('NULL AS "Return_ID"')
			->select('item.product_id AS "Product_ID"')
			->select('item.product_name AS "Product"')
			->select('item.options AS "Option"')
			->select('country AS "Country"')
			->select('user.forename AS "User_Forename"')
			->select('user.surname AS "User_Surname"')
			->select('user.email AS "Email"')
			->select('order_summary.user_id AS "User_id"')
			->from('order_item AS item')
			->join('order_summary', 'item.order_id = order_summary.order_id')
			->leftJoin('order_address', 'order_summary.order_id = order_address.order_id AND order_address.type = "delivery"') //  AND order_address.deleted_at IS NULL
			->leftJoin('return_item', 'return_item.exchange_item_id = item.item_id')
			->leftJoin('user', 'order_summary.user_id = user.user_id')
			->where('order_summary.status_code >= 0')
			->where('item.product_id NOT IN (9)')
			->where('return_item.exchange_item_id IS NULL')
			->where('item.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		return $data;
	}
}