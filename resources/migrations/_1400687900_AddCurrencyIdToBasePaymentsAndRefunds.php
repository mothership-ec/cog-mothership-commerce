<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1400687900_AddCurrencyIdToBasePaymentsAndRefunds extends Migration
{
	public function up()
	{
		$this->run("ALTER TABLE `refund` ADD `currency_id` CHAR(3)  NOT NULL  DEFAULT ''  AFTER `deleted_by`;");
		$this->run("ALTER TABLE `payment` ADD `currency_id` CHAR(3)  NOT NULL  DEFAULT ''  AFTER `deleted_by`;");
	}

	public function down()
	{
		$this->run("ALTER TABLE `refund` DROP `currency_id`;");
		$this->run("ALTER TABLE `payment` DROP `currency_id`;");
	}
}