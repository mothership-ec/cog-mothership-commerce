<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1402936340_DefaultBarcodesToUnitId extends Migration
{
	public function up()
	{
		// Sets barcode to equal unit_id value if null or empty string
		$this->run("
			UPDATE
				product_unit
			SET
				barcode = unit_id
			WHERE
				COALESCE(barcode, '') = '';
		");

		// Do not allow null on barcode
		$this->run("
			ALTER TABLE
				product_unit
			MODIFY
				barcode VARCHAR(13) NOT NULL;
		");
	}

	public function down()
	{
		// no down task necessary
	}
}