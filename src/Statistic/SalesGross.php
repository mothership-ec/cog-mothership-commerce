<?php

namespace Message\Mothership\Commerce\Statistic;

use Message\Mothership\ControlPanel\Statistic\AbstractDataset;

class SalesGross extends AbstractDataset
{
	public function getName()
	{
		return 'sales.gross';
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
				dataset = 'sales.gross';
		");

		$this->_query->run("
			INSERT INTO
				statistic
			SELECT
				'sales.gross',
				'sales.gross',
				created_at - MOD(created_at, 60 * 60 * 24) as day_start,
				SUM(total_gross),
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