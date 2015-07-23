<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1437641846_DiscountTypeField extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE
				order_discount
			ADD
				`type` VARCHAR(255) DEFAULT NULL
			AFTER
				created_by
		");

		$this->run("
			UPDATE
				order_discount
			SET
				`type` = 'code'
			WHERE
				code IS NOT NULL
			");
	}

	public function down()
	{
		$this->run("
			ALTER TABLE
				order_discount
			DROP COLUMN
				code
		");
	}
}