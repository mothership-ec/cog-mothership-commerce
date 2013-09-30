<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1380535234_ChangeProductImageIDToHash extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE `product_image`
			CHANGE `image_id` `image_id` VARCHAR(32)  NOT NULL  DEFAULT '';
		");
	}

	public function down()
	{
		$this->run('
			ALTER TABLE `product_image`
			CHANGE `image_id` `image_id` INT(11)  UNSIGNED  NOT NULL  AUTO_INCREMENT;
		');
	}
}