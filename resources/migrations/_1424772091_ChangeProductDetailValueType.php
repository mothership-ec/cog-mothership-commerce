<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1424772091_ChangeProductDetailValueType extends Migration
{
	public function up()
	{
		$this->run("ALTER TABLE product_detail MODIFY `value` TEXT;");
	}

	public function down()
	{
		$this->run("ALTER TABLE product_detail MODIFY `value` VARCHAR(255);");
	}
}