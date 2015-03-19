<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1426241749_ProductImageOptionsImageIDFix extends Migration
{
	public function up()
	{
		$this->run("ALTER TABLE product_image_option MODIFY `image_id` VARCHAR(32);");
	}

	public function down()
	{
		$this->run("ALTER TABLE product_image_option MODIFY `image_id` INT;");
	}
}