<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1423739640_AddExportCode extends Migration
{
	public function up()
	{
		$this->run("ALTER TABLE product_export ADD export_code VARCHAR(255) DEFAULT NULL;");
	}

	public function down()
	{
		$this->run("ALTER TABLE product_export DROP COLUMN export_code;");
	}
}