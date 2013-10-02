<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1379411228_SetUpOrders extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE IF NOT EXISTS `order_address` (
			  `address_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) unsigned NOT NULL,
			  `type` varchar(20) NOT NULL DEFAULT 'unknown',
			  `title` varchar(255) DEFAULT NULL,
			  `forename` varchar(255) DEFAULT NULL,
			  `surname` varchar(255) DEFAULT NULL,
			  `line_1` varchar(255) DEFAULT NULL,
			  `line_2` varchar(255) DEFAULT NULL,
			  `line_3` varchar(255) DEFAULT NULL,
			  `line_4` varchar(255) DEFAULT NULL,
			  `postcode` varchar(100) DEFAULT NULL,
			  `country` varchar(100) DEFAULT NULL,
			  `country_id` char(2) DEFAULT NULL,
			  `telephone` varchar(100) DEFAULT NULL,
			  `town` varchar(100) DEFAULT NULL,
			  `state_id` varchar(2) DEFAULT NULL,
			  `state` varchar(100) DEFAULT NULL,
			  PRIMARY KEY (`address_id`),
			  KEY `type` (`type`),
			  KEY `order_id` (`order_id`),
			  KEY `state_id` (`state_id`),
			  KEY `country_id` (`country_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_discount` (
			  `discount_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) unsigned NOT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `code` varchar(100) DEFAULT NULL,
			  `amount` decimal(10,2) unsigned NOT NULL,
			  `percentage` decimal(5,2) unsigned DEFAULT NULL,
			  `name` varchar(255) DEFAULT NULL,
			  `description` text,
			  PRIMARY KEY (`discount_id`),
			  KEY `order_id` (`order_id`),
			  KEY `discount_id` (`code`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_dispatch` (
			  `dispatch_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) unsigned NOT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `updated_at` int(11) unsigned DEFAULT NULL,
			  `updated_by` int(11) unsigned DEFAULT NULL,
			  `shipped_at` int(11) unsigned DEFAULT NULL,
			  `shipped_by` int(11) unsigned DEFAULT NULL,
			  `method` varchar(100) NOT NULL DEFAULT '',
			  `code` varchar(100) DEFAULT NULL,
			  `cost` decimal(10,2) unsigned DEFAULT NULL,
			  `weight_grams` int(11) unsigned DEFAULT NULL,
			  PRIMARY KEY (`dispatch_id`),
			  KEY `order_id` (`order_id`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`),
			  KEY `method` (`method`),
			  KEY `code` (`code`),
			  KEY `updated_at` (`updated_at`),
			  KEY `updated_by` (`updated_by`),
			  KEY `shipped_at` (`shipped_at`),
			  KEY `shipped_by` (`shipped_by`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_dispatch_item` (
			  `dispatch_id` int(11) unsigned NOT NULL,
			  `item_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`item_id`,`dispatch_id`),
			  KEY `despatch_id` (`dispatch_id`),
			  KEY `item_id` (`item_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_document` (
			  `document_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) unsigned NOT NULL,
			  `dispatch_id` int(11) unsigned DEFAULT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `type` varchar(100) NOT NULL DEFAULT '',
			  `url` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`document_id`),
			  KEY `order_id` (`order_id`),
			  KEY `dispatch_id` (`dispatch_id`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`),
			  KEY `type` (`type`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_item` (
			  `item_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) unsigned NOT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `list_price` decimal(10,2) unsigned NOT NULL COMMENT 'The price shown on the website for this product at the time of purchase',
			  `net` decimal(10,2) unsigned NOT NULL COMMENT 'The net amount paid for the item',
			  `discount` decimal(10,2) unsigned NOT NULL COMMENT 'The discount amount associated with this item',
			  `tax` decimal(10,2) unsigned NOT NULL COMMENT 'The tax amount for this item (calculated on (net - discount))',
			  `gross` decimal(10,2) unsigned NOT NULL COMMENT 'The gross amount paid for this item ((net - discount) + tax)',
			  `rrp` decimal(10,2) unsigned DEFAULT NULL,
			  `tax_rate` decimal(4,2) unsigned NOT NULL COMMENT 'The tax rate for this item',
			  `product_tax_rate` decimal(4,2) unsigned NOT NULL,
			  `tax_strategy` varchar(10) NOT NULL DEFAULT 'inclusive',
			  `product_id` int(11) unsigned DEFAULT NULL,
			  `product_name` varchar(255) DEFAULT NULL,
			  `unit_id` int(11) unsigned DEFAULT NULL,
			  `unit_revision` int(11) unsigned DEFAULT NULL,
			  `sku` varchar(100) DEFAULT NULL,
			  `barcode` varchar(13) DEFAULT NULL,
			  `options` varchar(255) DEFAULT NULL COMMENT 'A list of the text labels for all options associated with the unit at the time of purchase',
			  `brand` varchar(255) DEFAULT NULL,
			  `weight_grams` int(11) unsigned DEFAULT NULL,
			  `stock_location` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`item_id`),
			  KEY `order_id` (`order_id`),
			  KEY `product_id` (`product_id`),
			  KEY `unit_id` (`unit_id`),
			  KEY `unit_revision` (`unit_revision`),
			  KEY `stock_location` (`stock_location`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_item_discount` (
			  `item_id` int(11) unsigned NOT NULL,
			  `discount_id` int(11) unsigned NOT NULL,
			  `amount` float(10,2) unsigned NOT NULL,
			  PRIMARY KEY (`discount_id`,`item_id`),
			  KEY `discount_id` (`discount_id`),
			  KEY `item_id` (`item_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_item_personalisation` (
			  `item_id` int(11) unsigned NOT NULL,
			  `sender_name` varchar(255) DEFAULT NULL,
			  `recipient_name` varchar(255) DEFAULT NULL,
			  `recipient_email` varchar(255) DEFAULT NULL,
			  `message` text,
			  PRIMARY KEY (`item_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_item_return` (
			  `return_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) unsigned NOT NULL,
			  `item_id` int(11) unsigned NOT NULL,
			  `document_id` int(11) unsigned NOT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `updated_at` int(11) unsigned DEFAULT NULL,
			  `updated_by` int(11) unsigned DEFAULT NULL,
			  `completed_at` int(11) unsigned DEFAULT NULL,
			  `completed_by` int(11) unsigned DEFAULT NULL,
			  `exchange_item_id` int(11) unsigned DEFAULT NULL,
			  `status_id` int(11) NOT NULL,
			  `reason` varchar(255) NOT NULL DEFAULT '',
			  `resolution` varchar(255) NOT NULL DEFAULT '',
			  `balance` decimal(10,2) DEFAULT NULL,
			  `calculated_balance` decimal(10,2) NOT NULL,
			  `accepted` tinyint(1) DEFAULT NULL,
			  `returned_value` decimal(10,2) unsigned DEFAULT NULL,
			  `return_to_stock_location_id` int(11) unsigned DEFAULT NULL,
			  PRIMARY KEY (`return_id`),
			  KEY `order_id` (`order_id`),
			  KEY `item_id` (`item_id`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`),
			  KEY `updated_at` (`updated_at`),
			  KEY `updated_by` (`updated_by`),
			  KEY `exchange_item_id` (`exchange_item_id`),
			  KEY `return_to_stock_location_id` (`return_to_stock_location_id`),
			  KEY `status_id` (`status_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_item_status` (
			  `order_id` int(11) unsigned NOT NULL,
			  `item_id` int(11) unsigned NOT NULL,
			  `status_code` int(6) NOT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  KEY `order_id` (`order_id`),
			  KEY `item_id` (`item_id`),
			  KEY `status_id` (`status_code`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_metadata` (
			  `order_id` int(11) unsigned NOT NULL,
			  `key` varchar(255) NOT NULL DEFAULT '',
			  `value` text,
			  PRIMARY KEY (`order_id`,`key`),
			  KEY `order_id` (`order_id`),
			  KEY `metadata_key` (`key`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_note` (
			  `note_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) unsigned NOT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `note` text NOT NULL,
			  `customer_notified` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `raised_from` varchar(100) DEFAULT NULL,
			  PRIMARY KEY (`note_id`),
			  KEY `order_id` (`order_id`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`),
			  KEY `raised_from` (`raised_from`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_payment` (
			  `payment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) unsigned NOT NULL,
			  `return_id` int(11) unsigned DEFAULT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `method` varchar(100) NOT NULL DEFAULT '',
			  `amount` decimal(10,2) unsigned NOT NULL,
			  `reference` text,
			  PRIMARY KEY (`payment_id`),
			  KEY `order_id` (`order_id`),
			  KEY `return_id` (`return_id`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`),
			  KEY `method` (`method`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_refund` (
			  `refund_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) unsigned NOT NULL,
			  `payment_id` int(11) unsigned DEFAULT NULL,
			  `return_id` int(11) unsigned DEFAULT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `method` varchar(100) NOT NULL,
			  `amount` decimal(10,2) unsigned NOT NULL,
			  `reason` varchar(255) DEFAULT NULL,
			  `reference` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`refund_id`),
			  KEY `order_id` (`order_id`),
			  KEY `payment_id` (`payment_id`),
			  KEY `return_id` (`return_id`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`),
			  KEY `method` (`method`),
			  KEY `reference` (`reference`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_shipping` (
			  `order_id` int(11) unsigned NOT NULL,
			  `list_price` decimal(10,2) unsigned NOT NULL,
			  `net` decimal(10,2) unsigned NOT NULL,
			  `discount` decimal(10,2) unsigned NOT NULL,
			  `tax` decimal(10,2) unsigned NOT NULL,
			  `tax_rate` decimal(4,2) unsigned NOT NULL,
			  `gross` decimal(10,2) unsigned NOT NULL,
			  `name` varchar(255) DEFAULT NULL,
			  KEY `order_id` (`order_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `order_summary` (
			  `order_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `updated_at` int(11) unsigned DEFAULT NULL,
			  `updated_by` int(11) unsigned DEFAULT NULL,
			  `status_code` int(6) NOT NULL,
			  `user_id` int(11) unsigned DEFAULT NULL,
			  `user_email` varchar(255) DEFAULT '',
			  `type` varchar(50) NOT NULL DEFAULT 'web',
			  `locale` varchar(50) NOT NULL,
			  `taxable` int(1) unsigned NOT NULL,
			  `currency_id` char(3) DEFAULT NULL,
			  `conversion_rate` decimal(19,9) unsigned NOT NULL DEFAULT '1.000000000',
			  `product_net` decimal(10,2) unsigned NOT NULL,
			  `product_discount` decimal(10,2) unsigned NOT NULL,
			  `product_tax` decimal(10,2) unsigned NOT NULL,
			  `product_gross` decimal(10,2) unsigned NOT NULL,
			  `total_net` decimal(10,2) unsigned NOT NULL,
			  `total_discount` decimal(10,2) unsigned NOT NULL,
			  `total_tax` decimal(10,2) unsigned NOT NULL,
			  `total_gross` decimal(10,2) unsigned NOT NULL,
			  PRIMARY KEY (`order_id`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`),
			  KEY `updated_at` (`updated_at`),
			  KEY `updated_by` (`updated_by`),
			  KEY `user_id` (`user_id`),
			  KEY `type` (`type`),
			  KEY `locale` (`locale`),
			  KEY `currency_id` (`currency_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `payment_dump` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `dump` blob,
			  `transaction_id` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run('
			DROP TABLE IF EXISTS
				`order_address`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_discount`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_dispatch`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_dispatch_item`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_document`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_item`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_item_discount`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_item_personalisation`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_item_return`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_item_status`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_metadata`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_note`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_payment`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_refund`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_shipping`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`order_summary`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`payment_dump`
		');
	}
}
