<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1396365308_UpdateTransactionUpdatedMetadata extends Migration
{
	public function up()
	{

		$this->run('ALTER TABLE `transaction` ADD `updated_at` int(11) unsigned DEFAULT NULL AFTER `created_by`');
		$this->run('ALTER TABLE `transaction` ADD `updated_by` int(11) unsigned DEFAULT NULL AFTER `updated_at`');
	}

	public function down()
	{
		$this->run('ALTER TABLE	`transaction` DROP `updated_at`');
		$this->run('ALTER TABLE	`transaction` DROP `updated_by`');
	}
}