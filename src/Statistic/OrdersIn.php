<?php

namespace Message\Mothership\Commerce\Statistic;

use Message\Mothership\ControlPanel\Statistic\AbstractDataset;

class OrdersIn extends AbstractDataset
{
	public function getName()
	{
		return 'orders.in';
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
				dataset = 'orders.in';
		");

		$this->_query->run("
			INSERT INTO
				statistic
			SELECT
				'orders.in',
				'orders.in',
				created_at - MOD(created_at, 60 * 60 * 24) as day_start,
				COUNT(order_id),
				UNIX_TIMESTAMP(NOW())
			FROM
				order_summary
			GROUP BY
				day_start;
		");

		if (! $this->_transOverriden) {
			$this->_query->commit();
		}
	}
}