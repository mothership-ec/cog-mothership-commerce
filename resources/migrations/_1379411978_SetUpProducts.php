<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1379411978_SetUpProducts extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE `product` (
			  `product_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `year` year(4) DEFAULT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `updated_at` int(11) unsigned DEFAULT NULL,
			  `updated_by` int(11) unsigned DEFAULT NULL,
			  `deleted_at` int(11) unsigned DEFAULT NULL,
			  `deleted_by` int(11) unsigned DEFAULT NULL,
			  `brand` varchar(255) DEFAULT '',
			  `name` varchar(255) NOT NULL DEFAULT '',
			  `tax_rate` decimal(4,2) unsigned DEFAULT NULL,
			  `tax_strategy` varchar(10) NOT NULL DEFAULT 'inclusive',
			  `supplier_ref` varchar(255) DEFAULT NULL,
			  `weight_grams` int(11) unsigned DEFAULT NULL,
			  `category` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`product_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_export` (
			  `product_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `locale` varchar(50) NOT NULL DEFAULT '',
			  `export_value` decimal(10,2) DEFAULT NULL,
			  `export_description` varchar(255) DEFAULT NULL,
			  `export_manufacture_country_id` varchar(2) NOT NULL DEFAULT 'GB',
			  PRIMARY KEY (`product_id`,`locale`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_image` (
			  `image_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `product_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `type` varchar(255) NOT NULL DEFAULT '',
			  `file_id` int(11) unsigned NOT NULL,
			  `locale` varchar(50) NOT NULL DEFAULT '',
			  PRIMARY KEY (`image_id`),
			  KEY `product_id` (`product_id`),
			  KEY `type` (`type`),
			  KEY `file_id` (`file_id`),
			  KEY `locale` (`locale`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_image_option` (
			  `image_id` int(11) unsigned NOT NULL,
			  `name` varchar(255) NOT NULL DEFAULT '',
			  `value` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`image_id`,`name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_info` (
			  `product_id` int(11) NOT NULL DEFAULT '0',
			  `locale` varchar(50) NOT NULL DEFAULT '',
			  `display_name` varchar(200) DEFAULT NULL,
			  `season` varchar(200) DEFAULT NULL,
			  `description` text,
			  `fabric` text,
			  `features` text,
			  `care_instructions` text,
			  `short_description` text,
			  `sizing` text,
			  `notes` text,
			  PRIMARY KEY (`product_id`,`locale`),
			  KEY `display_name` (`display_name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_price` (
			  `product_id` int(11) unsigned NOT NULL,
			  `type` char(50) NOT NULL DEFAULT '',
			  `price` decimal(10,2) unsigned NOT NULL,
			  `currency_id` varchar(50) NOT NULL DEFAULT '',
			  `locale` varchar(50) NOT NULL DEFAULT '',
			  PRIMARY KEY (`product_id`,`type`,`currency_id`,`locale`),
			  KEY `type` (`type`),
			  KEY `currency_id` (`currency_id`),
			  KEY `locale` (`locale`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_tag` (
			  `product_id` int(11) NOT NULL DEFAULT '0',
			  `name` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`product_id`,`name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_unit` (
			  `product_id` int(11) unsigned DEFAULT NULL,
			  `unit_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `visible` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `barcode` varchar(13) DEFAULT '',
			  `supplier_ref` varchar(255) DEFAULT NULL,
			  `weight_grams` int(11) unsigned DEFAULT NULL,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned NOT NULL,
			  `updated_at` int(11) unsigned DEFAULT NULL,
			  `updated_by` int(11) unsigned DEFAULT NULL,
			  `deleted_at` int(11) unsigned DEFAULT NULL,
			  `deleted_by` int(11) unsigned DEFAULT NULL,
			  PRIMARY KEY (`unit_id`),
			  KEY `catalogue_id` (`product_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_unit_info` (
			  `unit_id` int(11) unsigned NOT NULL,
			  `revision_id` int(11) NOT NULL DEFAULT '1',
			  `sku` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`unit_id`,`revision_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_unit_option` (
			  `unit_id` int(11) unsigned NOT NULL,
			  `option_name` varchar(255) NOT NULL DEFAULT '',
			  `option_value` varchar(255) DEFAULT NULL,
			  `revision_id` int(11) NOT NULL DEFAULT '1',
			  PRIMARY KEY (`unit_id`,`option_name`,`revision_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_unit_price` (
			  `unit_id` int(11) unsigned NOT NULL,
			  `type` char(50) NOT NULL DEFAULT '',
			  `price` decimal(10,2) unsigned NOT NULL,
			  `currency_id` varchar(50) NOT NULL DEFAULT '',
			  `locale` varchar(50) NOT NULL DEFAULT '',
			  PRIMARY KEY (`unit_id`,`type`,`currency_id`,`locale`),
			  KEY `type` (`type`),
			  KEY `currency_id` (`currency_id`),
			  KEY `locale` (`locale`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `product_unit_stock` (
			  `unit_id` int(11) NOT NULL DEFAULT '0',
			  `location` varchar(50) NOT NULL DEFAULT '',
			  `stock` int(11) DEFAULT NULL,
			  PRIMARY KEY (`unit_id`,`location`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run('
			DROP TABLE IF EXISTS
				`product`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_export`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_image`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_image_option`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_info`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_price`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_tag`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_unit`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_unit_info`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_unit_option`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_unit_price`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`product_unit_stock`
		');
	}
}
