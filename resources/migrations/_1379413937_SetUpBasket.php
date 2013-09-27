<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1379413937_SetUpBasket extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE IF NOT EXISTS `basket` (
			  `basket_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `user_id` int(10) unsigned DEFAULT NULL,
			  `contents` blob NOT NULL,
			  `created_at` int(10) unsigned NOT NULL,
			  `updated_at` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`basket_id`),
			  KEY `user_id` (`user_id`),
			  KEY `created_at` (`created_at`),
			  KEY `updated_at` (`updated_at`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run('
			DROP TABLE IF EXISTS
				`basket`
		');
	}
}
