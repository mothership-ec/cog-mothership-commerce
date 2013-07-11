<?php

namespace Message\Mothership\Commerce\Order;

use Message\Cog\DB;

/**
 * Decorator for loading orders.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader
{
	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	public function getByID($id)
	{
		return $this->_load($id);
	}

	protected function _load($id)
	{

	}
// taken from Order::load()
/*
		$query = '
			SELECT
				UNIX_TIMESTAMP(order_datetime) AS placedTimestamp,
				UNIX_TIMESTAMP(order_updated)  AS updateTimestamp,
				order_total                    AS total,
				order_discount                 AS discount,
				order_taxable                  AS taxable,
				order_tax                      AS tax,
				order_tax_discount             AS taxDiscount,
				order_payment                  AS paid,
				order_change                   AS `change`,
				order_summary.user_id          AS userID,
				IF(
					user_forename IS NOT NULL,
					CONCAT_WS(" ", user_forename, user_surname),
					"unknown"
				)                              AS userName,
				currency_id                    AS currencyID,
				currency_name                  AS currencySymbol,
				shipping_id                    AS shippingID,
				shipping_name                  AS shippingName,
				shipping_amount                AS shippingAmount,
				shipping_tax                   AS shippingTax,
				shop_id                        AS shopID,
				shop.name                      AS shopName,
				till_id                        AS tillID,
				staff_id                       AS staffID,
				status_id                      AS statusID,
				status_name                    AS statusName
			FROM
				order_summary
			LEFT JOIN
				order_shipping USING (order_id)
			JOIN
				order_status_name USING (status_id)
			JOIN
				val_currency USING (currency_id)
			LEFT JOIN
				val_user ON order_summary.user_id = val_user.user_id
			LEFT JOIN
				order_pos USING (order_id)
			LEFT JOIN
				shop USING (shop_id)
			WHERE
				order_id = ' . $this->orderID;
		$DB = new DBquery($query);
		if ($row = $DB->row()) {
			foreach ($row as $key => $val) {
				$this->{$key} = $val;
			}
		} else {
			throw new OrderException('Unable to retrieve order #' . $this->orderID);
		}

 */
}