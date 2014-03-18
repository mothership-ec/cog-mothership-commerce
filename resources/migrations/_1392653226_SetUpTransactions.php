<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1392653226_SetUpTransactions extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE `transaction` (
			  `transaction_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `type` varchar(255) NOT NULL DEFAULT '',
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `voided_at` int(11) unsigned DEFAULT NULL,
			  `voided_by` int(11) unsigned DEFAULT NULL,
			  PRIMARY KEY (`transaction_id`)
			) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `transaction_record` (
			  `transaction_id` int(11) unsigned NOT NULL,
			  `record_id` int(11) unsigned NOT NULL,
			  `type` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`transaction_id`,`record_id`,`type`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `transaction_attribute` (
			  `transaction_id` int(11) unsigned NOT NULL,
			  `name` varchar(255) NOT NULL DEFAULT '',
			  `value` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`transaction_id`,`name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run('
			DROP TABLE IF EXISTS
				`transaction`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`transaction_record`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`transaction_attribute`
		');
	}
}
