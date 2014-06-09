<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1392719777_DatabaseDeletedColumns extends Migration
{
	public function up()
	{
		$this->run("ALTER TABLE order_summary ADD deleted_at INT(11) UNSIGNED  NULL  DEFAULT NULL  AFTER created_by; ");
		$this->run("ALTER TABLE order_summary ADD deleted_by INT(11) UNSIGNED  NULL  DEFAULT NULL  AFTER deleted_at; ");
		$this->run("ALTER TABLE order_item ADD deleted_at INT(11) UNSIGNED  NULL  DEFAULT NULL  AFTER created_by; ");
		$this->run("ALTER TABLE order_item ADD deleted_by INT(11) UNSIGNED  NULL  DEFAULT NULL  AFTER deleted_at; ");
		$this->run("ALTER TABLE order_refund ADD deleted_at INT(11) UNSIGNED  NULL  DEFAULT NULL  AFTER created_by; ");
		$this->run("ALTER TABLE order_refund ADD deleted_by INT(11) UNSIGNED  NULL  DEFAULT NULL  AFTER deleted_at; ");
		$this->run("ALTER TABLE order_payment ADD deleted_at INT(11) UNSIGNED  NULL  DEFAULT NULL  AFTER created_by; ");
		$this->run("ALTER TABLE order_payment ADD deleted_by INT(11) UNSIGNED  NULL  DEFAULT NULL  AFTER deleted_at; ");
	}

	public function down()
	{
		$this->run("ALTER TABLE order_summary DROP COLUMN deleted_at; ");
		$this->run("ALTER TABLE order_summary DROP COLUMN deleted_by; ");
		$this->run("ALTER TABLE order_item DROP COLUMN deleted_at; ");
		$this->run("ALTER TABLE order_item DROP COLUMN deleted_by; ");
		$this->run("ALTER TABLE order_refund DROP COLUMN deleted_at; ");
		$this->run("ALTER TABLE order_refund DROP COLUMN deleted_by; ");
		$this->run("ALTER TABLE order_payment DROP COLUMN deleted_at; ");
		$this->run("ALTER TABLE order_payment DROP COLUMN deleted_by; ");
	}
}
