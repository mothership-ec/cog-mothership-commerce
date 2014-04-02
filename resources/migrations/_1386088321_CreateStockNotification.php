<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1386088321_CreateStockNotification extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE IF NOT EXISTS `stock_notification` (
			  `notification_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `unit_id` int(11) unsigned NOT NULL,
			  `type` varchar(255) NOT NULL,
			  `email` varchar(255) NOT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned NULL,
			  `notified_at` int(11) unsigned NULL,
			  PRIMARY KEY (`notification_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run('
			DROP TABLE IF EXISTS
				`stock_notification`
		');
	}
}
