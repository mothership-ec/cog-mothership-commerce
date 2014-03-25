<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1395164293_AddOrderPaymentChange extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE `order_payment` ADD `change` DECIMAL(10,2)  UNSIGNED  NULL  DEFAULT NULL  AFTER `amount`;
		");
	}

	public function down()
	{
		$this->run('
			ALTER TABLE `order_payment` DROP COLUMN `change`;
		');
	}
}
