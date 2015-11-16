<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

/**
 * Class _1444911212_AllowNullOnBarcode
 *
 * @author Thomas Marchant <thomas@mothership.ec>
 *
 * Allow barcodes to be null again, essential as barcodes are generated after the unit is saved
 */
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