<?php

namespace Message\Mothership\Commerce\Statistic;

use Message\Mothership\ControlPanel\Statistic\AbstractDataset;

class OrdersOut extends AbstractDataset
{
	public function getName()
	{
		return 'orders.out';
	}

	public function getPeriodLength()
	{
		return static::DAILY;
	}

	public function rebuild()
	{
		$this->_query->run("
			DELETE FROM
				statistic
			WHERE
				dataset = 'orders.out';
		");

		$this->_query->run("
			INSERT INTO
				statistic
			SELECT
				'orders.out',
				'orders.out',
				created_at - MOD(created_at, 60 * 60 * 24) as day_start,
				COUNT(DISTINCT(order_id)) as count_orders,
				UNIX_TIMESTAMP(NOW())
			FROM
				order_item_status
			WHERE
				status_code = 1000
			GROUP BY
				day_start;
		");

		if (! $this->_transOverriden) {
			$this->_query->commit();
		}
	}
}