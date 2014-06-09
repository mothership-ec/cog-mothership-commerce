<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1401715946_AddOrderItemBasePrice extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE `order_item`
			ADD COLUMN `base_price` DECIMAL(10,2) unsigned NOT NULL
				COMMENT 'The base price for this item for this order - this is the price shown to the customer in checkout and the price on which discounts are calculated. It is calculated from the actual price.'
				AFTER `actual_price`;
		");

		$this->run("
			UPDATE
				`order_item`
			SET
				`base_price` = IF(
					`tax_rate` <= 0 AND 'inclusive' = `tax_strategy`,
					`actual_price` - ROUND(((`actual_price` / (100 + `product_tax_rate`)) * `product_tax_rate`), 2),
					`actual_price`
				)
			WHERE
				`base_price` = 0
		");
	}

	public function down()
	{
		$this->run('ALTER TABLE `order_item` DROP COLUMN `base_price`;');
	}
}
