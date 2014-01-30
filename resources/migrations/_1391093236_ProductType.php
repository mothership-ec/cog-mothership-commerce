<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1391093236_ProductType extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE
				product_detail
				(
					product_id INT(11) NOT NULL,
					name VARCHAR(255) NOT NULL,
					value VARCHAR(255),
					PRIMARY KEY (product_id, name)
				);
		");

		$this->run("
			ALTER TABLE
				product
			ADD
				type VARCHAR(30)
			AFTER
				deleted_by
		");
	}

	public function down()
	{
		$this->run("
			ALTER TABLE
				product
			DROP
				type
		");

		$this->run("
			DROP TABLE
				product_detail;
		");
	}
}