<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1396435931_AddStockNotificationCreatedBy extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE `stock_notification`
			ADD COLUMN `created_by` int(11) unsigned NULL AFTER `created_at`;
		");
	}

	public function down()
	{
		$this->run('
			ALTER TABLE `stock_notification`
			DROP COLUMN `created_by`;
		');
	}
}
