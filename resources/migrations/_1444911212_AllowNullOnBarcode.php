<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1444911212_AllowNullOnBarcode extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE
				product_unit
			MODIFY
				barcode VARCHAR(13);
		");
	}

	public function down()
	{
		$this->run("
			ALTER TABLE
				product_unit
			MODIFY
				barcode VARCHAR(13) NOT NULL;
		");
	}
}