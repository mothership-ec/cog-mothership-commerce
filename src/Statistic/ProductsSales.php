<?php

namespace Message\Mothership\Commerce\Statistic;

use Message\Mothership\ControlPanel\Statistic\AbstractDataset;

class ProductsSales extends AbstractDataset
{
	public function getName()
	{
		return 'products.sales';
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
				dataset = 'products.sales';
		");

		$this->_query->run("
			INSERT INTO
				statistic
			SELECT
				*
			FROM (
				SELECT
					'products.sales' as dataset,
					CONCAT('products.sales.', product_id) as `key`,
					created_at - MOD(created_at, 60 * 60 * 24) as period,
					COUNT(product_id) as `value`,
					UNIX_TIMESTAMP(NOW()) as created_at
				FROM
					order_item
				GROUP BY
					product_id, `period`
			) as products
			WHERE
				products.value > 0;
		");

		if (! $this->_transOverriden) {
			$this->_query->commit();
		}
	}
}