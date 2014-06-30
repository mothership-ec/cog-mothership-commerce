<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1400599470_SortNameField extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE
				product_info
			ADD
				sort_name VARCHAR (255)
			AFTER
				display_name;
		");
	}

	public function down()
	{
		$this->run("
			ALTER TABLE
				product_info
			DROP
				sort_name;
		");
	}
}