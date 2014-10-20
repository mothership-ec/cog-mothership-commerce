<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1405516571_MakeDispatchesDeletable extends Migration
{
	public function up()
	{
		$this->run("ALTER TABLE order_dispatch ADD deleted_at INT(11) UNSIGNED  NULL  DEFAULT NULL  AFTER created_by; ");
		$this->run("ALTER TABLE order_dispatch ADD deleted_by INT(11) UNSIGNED  NULL  DEFAULT NULL  AFTER deleted_at; ");
	}

	public function down()
	{
		$this->run("ALTER TABLE order_dispatch DROP COLUMN deleted_at; ");
		$this->run("ALTER TABLE order_dispatch DROP COLUMN deleted_by; ");
	}
}