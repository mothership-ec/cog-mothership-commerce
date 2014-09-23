<?php

namespace Message\Mothership\Commerce\Statistic;

use Message\Mothership\ControlPanel\Statistic\AbstractDataset;

class SalesNet extends AbstractDataset
{
	public function getName()
	{
		return 'sales.net';
	}

	public function getPeriodLength()
	{
		return static::HOURLY;
	}

	public function rebuild()
	{
		$this->_query->run("
			DELETE FROM
				statistic
			WHERE
				dataset = 'sales.net';
		");

		$this->_query->run("
			INSERT INTO
				statistic
			SELECT
				'sales.net',
				'sales.net',
				created_at - MOD(created_at, 60 * 60 * 24) as day_start,
				SUM(total_net),
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