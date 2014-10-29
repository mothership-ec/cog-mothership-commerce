<?php

namespace Message\Mothership\Commerce\Report\SalesReport;

use Message\Cog\DB;

class ShippingInData implements QueryInterface
{
	private $_to;
	private $_from;

	/**
	 * Gets the query as an string
	 *
	 * @param  string | null     $from      Date from which to get data
	 * @param  string | null     $to        Get data to this date
	 *
	 * @return string
	 */
	public function getQueryString($from = null, $to = null)
	{
		$selectExprs = [
			'order_summary.created_at AS date',
			'(IFNULL(net, 0)) AS net',
			'(IFNULL(tax, 0)) AS tax',
			'(IFNULL(gross, 0)) AS gross',
			'"Shipping In" AS `type`',
			'"" AS item_id',
			'order_shipping.order_id AS order_id',
			'"" AS product',
			'"" AS `option`',
		];

		if ($from && $to) {
			$this->_from = $from;
			$this->_to = $to;
		} else {
			$this->_from = "UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH))";
			$this->_to = "UNIX_TIMESTAMP(NOW())";
		}

		$shippingIn =
			$this->QueryBuilder
				->select($selectExprs)
				->from("order_shipping")
				->join("order_summary","order_shipping.order_id = order_summary.order_id")
				->where("order_summary.status_code >= 0")
				->where("order_summary.created_at BETWEEN " . $this->_from . " AND " .  $this->_to);

		return $shippingIn;

	}
}