<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1410360627_DefaultSkusToUnitID extends Migration
{
	public function up()
	{
		// Sets barcode to equal unit_id value if null or empty string
		$this->run("
			UPDATE
				product_unit_info
			SET
				sku = unit_id
			WHERE
				COALESCE(sku, '') = '';
		");

		// Do not allow null on barcode
		$this->run("
			ALTER TABLE
				product_unit_info
			MODIFY
				sku VARCHAR(13) NOT NULL;
		");
	}

	public function down()
	{
		// no down task necessary
	}
}