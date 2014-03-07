<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1394204695_UpdateTransactions extends Migration
{

	public function up()
	{
		$this->run("ALTER TABLE `transaction` DROP COLUMN `till_id`");
		$this->run("ALTER TABLE `transaction` DROP COLUMN `branch_id`");
		$this->run("ALTER TABLE `transaction` MODIFY COLUMN `created_at` int(11) NOT NULL");
		$this->run("ALTER TABLE `transaction` MODIFY COLUMN `type` varchar(20) NOT NULL");
	}

	public function down()
	{
		$this->run("ALTER TABLE `transaction` ADD COLUMN `branch_id` int(11) NOT NULL");
		$this->run("ALTER TABLE `transaction` ADD COLUMN `till_id` int(11) NOT NULL");
		$this->run("ALTER TABLE `transaction` MODIFY COLUMN `created_at` int(11) DEFAULT NULL");
		$this->run("ALTER TABLE `transaction` MODIFY COLUMN `type` NOT NULL DEFAULT ''");
	}
}