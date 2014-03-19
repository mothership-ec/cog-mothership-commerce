<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1392896459_AddOrderItemActualPrice extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE `order_item`
			ADD COLUMN `actual_price` DECIMAL(10,2) unsigned NOT NULL
				COMMENT 'The price for this item for this order - usually the same as list price unless it has been overridden or changed'
				AFTER `list_price`;
		");

		$this->run("
			UPDATE
				`order_item`
			SET
				`actual_price` = `list_price`
			WHERE
				`actual_price` = 0
		");
	}

	public function down()
	{
		$this->run('
			ALTER TABLE `order_item`
			DROP COLUMN `actual_price`;
		');
	}
}
