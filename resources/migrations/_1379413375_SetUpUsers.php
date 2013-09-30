<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1379413375_SetUpUsers extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE IF NOT EXISTS `user_address` (
			  `address_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `user_id` int(11) unsigned NOT NULL,
			  `type` varchar(255) NOT NULL DEFAULT '',
			  `created_at` int(11) unsigned DEFAULT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `updated_at` int(11) unsigned DEFAULT NULL,
			  `updated_by` int(11) unsigned DEFAULT NULL,
			  `line_1` varchar(255) NOT NULL DEFAULT '',
			  `line_2` varchar(255) DEFAULT NULL,
			  `line_3` varchar(255) DEFAULT NULL,
			  `line_4` varchar(255) DEFAULT NULL,
			  `town` char(255) NOT NULL DEFAULT '',
			  `postcode` varchar(50) NOT NULL,
			  `state_id` char(2) DEFAULT NULL,
			  `country_id` char(2) NOT NULL DEFAULT '',
			  `telephone` varchar(30) DEFAULT '',
			  PRIMARY KEY (`address_id`),
			  KEY `state_id` (`state_id`),
			  KEY `country_id` (`country_id`),
			  KEY `user_id` (`user_id`),
			  KEY `type` (`type`),
			  KEY `address_id` (`address_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run('
			DROP TABLE IF EXISTS
				`user_address`
		');
	}
}
