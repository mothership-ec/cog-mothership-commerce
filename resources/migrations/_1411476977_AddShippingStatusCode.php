<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1411476977_AddShippingStatusCode extends Migration
{
	public function up()
	{
		$this->run("ALTER TABLE order_shipping ADD status_code INT(11) SIGNED  NULL  DEFAULT NULL; ");
	}

	public function down()
	{
		$this->run("ALTER TABLE order_shipping DROP COLUMN status_code; ");
	}
}