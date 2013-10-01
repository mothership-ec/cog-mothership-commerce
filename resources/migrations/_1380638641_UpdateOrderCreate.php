<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1379411228_SetUpOrders extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE
				`order_shipping`
			ADD
				`display_name` VARCHAR(255)  NULL  DEFAULT NULL  AFTER `name`;

		");
	}

	public function down()
	{
		$this->run('
			ALTER TABLE `order_shipping` DROP `display_name`;
		');
	}
}
