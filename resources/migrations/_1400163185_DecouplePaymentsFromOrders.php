<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1400163185_DecouplePaymentsFromOrders extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE `payment` (
			  `payment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `deleted_at` int(11) unsigned DEFAULT NULL,
			  `deleted_by` int(11) unsigned DEFAULT NULL,
			  `method` varchar(100) NOT NULL DEFAULT '',
			  `amount` decimal(10,2) unsigned NOT NULL,
			  `change` decimal(10,2) unsigned DEFAULT NULL,
			  `reference` text,
			  PRIMARY KEY (`payment_id`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`),
			  KEY `method` (`method`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run('
			INSERT INTO
				`payment` (
					payment_id,
					created_at,
					created_by,
					deleted_at,
					deleted_by,
					method,
					amount,
					`change`,
					reference
				)
			SELECT
				payment_id,
				created_at,
				created_by,
				deleted_at,
				deleted_by,
				method,
				amount,
				`change`,
				reference
			FROM
				`order_payment`
		');

		$this->run('ALTER TABLE	`order_payment` DROP `return_id`');
		$this->run('ALTER TABLE	`order_payment` DROP `created_at`');
		$this->run('ALTER TABLE	`order_payment` DROP `created_by`');
		$this->run('ALTER TABLE	`order_payment` DROP `deleted_at`');
		$this->run('ALTER TABLE	`order_payment` DROP `deleted_by`');
		$this->run('ALTER TABLE	`order_payment` DROP `method`');
		$this->run('ALTER TABLE	`order_payment` DROP `amount`');
		$this->run('ALTER TABLE	`order_payment` DROP `change`');
		$this->run('ALTER TABLE	`order_payment` DROP `reference`');
	}

	public function down()
	{
		$this->run('ALTER TABLE	`order_payment` ADD COLUMN `return_id` int(11) unsigned DEFAULT NULL');
		$this->run('ALTER TABLE	`order_payment` ADD COLUMN `created_at` int(11) unsigned NOT NULL');
		$this->run('ALTER TABLE	`order_payment` ADD COLUMN `created_by` int(11) unsigned DEFAULT NULL');
		$this->run('ALTER TABLE	`order_payment` ADD COLUMN `deleted_at` int(11) unsigned DEFAULT NULL');
		$this->run('ALTER TABLE	`order_payment` ADD COLUMN `deleted_by` int(11) unsigned DEFAULT NULL');
		$this->run('ALTER TABLE	`order_payment` ADD COLUMN `method` varchar(100) NOT NULL');
		$this->run('ALTER TABLE	`order_payment` ADD COLUMN `amount` decimal(10,2) unsigned NOT NULL');
		$this->run('ALTER TABLE	`order_payment` ADD COLUMN `change` decimal(10,2) unsigned DEFAULT NULL');
		$this->run('ALTER TABLE	`order_payment` ADD COLUMN `reference` varchar(255) DEFAULT NULL');

		$this->run('
			UPDATE
				`order_payment`
			JOIN
				`payment` USING (payment_id)
			SET
				order_payment.created_at = payment.created_at,
				order_payment.created_by = payment.created_by,
				order_payment.deleted_at = payment.deleted_at,
				order_payment.deleted_by = payment.deleted_by,
				order_payment.method     = payment.method,
				order_payment.amount     = payment.amount,
				order_payment.`change`   = payment.`change`,
				order_payment.reference  = payment.reference
		');

		$this->run('DROP TABLE `payment`');
	}
}