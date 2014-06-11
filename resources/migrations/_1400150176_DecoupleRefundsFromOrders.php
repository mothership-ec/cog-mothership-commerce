<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1400150176_DecoupleRefundsFromOrders extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE `refund` (
			  `refund_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `payment_id` int(11) unsigned DEFAULT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `deleted_at` int(11) unsigned DEFAULT NULL,
			  `deleted_by` int(11) unsigned DEFAULT NULL,
			  `method` varchar(100) NOT NULL DEFAULT '',
			  `amount` decimal(10,2) unsigned NOT NULL,
			  `reason` varchar(255) DEFAULT NULL,
			  `reference` text,
			  PRIMARY KEY (`refund_id`),
			  KEY `payment_id` (`payment_id`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`),
			  KEY `deleted_at` (`deleted_at`),
			  KEY `deleted_by` (`deleted_by`),
			  KEY `method` (`method`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run('
			INSERT INTO
				`refund` (
					refund_id,
					payment_id,
					created_at,
					created_by,
					deleted_at,
					deleted_by,
					method,
					amount,
					reason,
					reference
				)
			SELECT
				refund_id,
				payment_id,
				created_at,
				created_by,
				deleted_at,
				deleted_by,
				method,
				amount,
				reason,
				reference
			FROM
				`order_refund`
		');

		$this->run('ALTER TABLE	`order_refund` DROP `payment_id`');
		$this->run('ALTER TABLE	`order_refund` DROP `return_id`');
		$this->run('ALTER TABLE	`order_refund` DROP `created_at`');
		$this->run('ALTER TABLE	`order_refund` DROP `created_by`');
		$this->run('ALTER TABLE	`order_refund` DROP `deleted_at`');
		$this->run('ALTER TABLE	`order_refund` DROP `deleted_by`');
		$this->run('ALTER TABLE	`order_refund` DROP `method`');
		$this->run('ALTER TABLE	`order_refund` DROP `amount`');
		$this->run('ALTER TABLE	`order_refund` DROP `reason`');
		$this->run('ALTER TABLE	`order_refund` DROP `reference`');
	}

	public function down()
	{
		$this->run('ALTER TABLE	`order_refund` ADD COLUMN `payment_id` int(11) unsigned DEFAULT NULL');
		$this->run('ALTER TABLE	`order_refund` ADD COLUMN `return_id` int(11) unsigned DEFAULT NULL');
		$this->run('ALTER TABLE	`order_refund` ADD COLUMN `created_at` int(11) unsigned NOT NULL');
		$this->run('ALTER TABLE	`order_refund` ADD COLUMN `created_by` int(11) unsigned DEFAULT NULL');
		$this->run('ALTER TABLE	`order_refund` ADD COLUMN `deleted_at` int(11) unsigned DEFAULT NULL');
		$this->run('ALTER TABLE	`order_refund` ADD COLUMN `deleted_by` int(11) unsigned DEFAULT NULL');
		$this->run('ALTER TABLE	`order_refund` ADD COLUMN `method` varchar(100) NOT NULL');
		$this->run('ALTER TABLE	`order_refund` ADD COLUMN `amount` decimal(10,2) unsigned NOT NULL');
		$this->run('ALTER TABLE	`order_refund` ADD COLUMN `reason` varchar(255) DEFAULT NULL');
		$this->run('ALTER TABLE	`order_refund` ADD COLUMN `reference` varchar(255) DEFAULT NULL');

		$this->run('
			UPDATE
				`order_refund`
			JOIN
				`refund` USING (refund_id)
			SET
				order_refund.payment_id = refund.payment_id,
				order_refund.created_at = refund.created_at,
				order_refund.created_by = refund.created_by,
				order_refund.deleted_at = refund.deleted_at,
				order_refund.deleted_by = refund.deleted_by,
				order_refund.method     = refund.method,
				order_refund.amount     = refund.amount,
				order_refund.reason     = refund.reason,
				order_refund.reference  = refund.reference
		');

		$this->run('DROP TABLE `refund`');
	}
}