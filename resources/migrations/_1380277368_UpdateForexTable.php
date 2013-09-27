<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1380277368_UpdateForexTable extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE `forex_rate`
			ADD `created_at` int(11) NOT NULL;
		");
	}

	public function down()
	{
		$this->run('
			ALTER TABLE `forex_rate`
			DROP `created_at`;
		');
	}
}
