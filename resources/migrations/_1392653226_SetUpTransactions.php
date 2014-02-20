<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1392653226_SetUpTransactions extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE IF NOT EXISTS `transaction` (
			  `transaction_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `type` varchar(20) NOT NULL DEFAULT '',
			  `created_at` int(11) DEFAULT NULL,
			  `created_by` int(11) DEFAULT NULL,
			  `voided_at` int(11) DEFAULT NULL,
			  `voided_by` int(11) DEFAULT NULL,
			  `branch_id` int(11) NOT NULL,
			  `till_id` int(11) NOT NULL,
			  PRIMARY KEY (`transaction_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `transaction_record` (
			  `transaction_id` int(11) unsigned NOT NULL,
			  `record_id` int(11) unsigned NOT NULL,
			  `type` int(11) DEFAULT NULL,
			  PRIMARY KEY (`transaction_id`,`record_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `transaction_attribute` (
			  `transaction_id` int(11) unsigned NOT NULL,
			  `attribute_name` varchar(20) NOT NULL DEFAULT '',
			  `attribute_value` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`transaction_id`)
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
