<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB;

class SalesData implements QueryBuilderInterface
{
	private $_to;
	private $_from;
	private $_voucherIDs = [];

	/**
	 * Gets the query as an string
	 *
	 * @todo : remove giftvouchers
	 * @todo : date filter
	 *
	 * @param  array | null      $voucherIDs  To voucherIDs if they are excluded
	 * @param  string | null     $from        Date from which to get data
	 * @param  string | null     $to          Get data to this date
	 *
	 * @return string
	 */
	public function getQueryString($voucherIDs = null,  $from = null, $to = null)
	{
		// if ($from && $to) {
		// 	$this->_from = $from;
		// 	$this->_to = $to;
		// } else {
		// 	$this->_from = "UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH))";
		// 	$this->_to = "UNIX_TIMESTAMP(NOW())";
		// }

		$salesQuery = $this->_builderFactory->getQueryBuilder();

		$salesQuery
			->select('item.created_at AS date')
			->select('order_summary.currency_id AS currency')
			->select('IFNULL(item.net, 0) AS net')
			->select('IFNULL(item.tax, 0) AS tax')
			->select('IFNULL(item.gross, 0) AS gross')
			->select('order_summary.type AS type')
			->select('item.item_id AS item_id')
			->select('item.order_id AS order_id')
			->select('item.product_id AS product_id')
			->select('item.product_name AS product')
			->select('item.options AS `option`')
			->select('country AS `country`')
			->select('CONCAT(user.forename," ",user.surname) AS `user`')
			->select('user.email AS `email`')
			->select('order_summary.user_id AS `user_id`')
			->from('order_item AS item')
			->join('order_summary', 'item.order_id = order_summary.order_id')
			->leftJoin('order_address', 'order_summary.order_id = order_address.order_id AND order_address.type = "delivery"')
			->leftJoin('return_item', 'return_item.exchange_item_id = item.item_id')
			->leftJoin('user', 'order_summary.user_id = user.user_id')
			->where('order_summary.status_code >= 0')
			->where('item.product_id NOT IN (9)')
			->where('return_item.exchange_item_id IS NULL')
			->where('item.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		return $salesQuery;
	}
}