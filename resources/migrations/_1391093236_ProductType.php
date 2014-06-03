<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1391093236_ProductType extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE
				product_detail
				(
					product_id INT(11) unsigned NOT NULL,
					name VARCHAR(255) NOT NULL,
					value VARCHAR(255),
					value_int INT(11),
					locale VARCHAR(50) DEFAULT 'EN',
					PRIMARY KEY (product_id, name)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			ALTER TABLE
				product
			ADD
				type VARCHAR(255) DEFAULT 'apparel'
			AFTER
				deleted_by
		");

		$this->run("
			INSERT INTO
				product_detail
				(
					product_id,
					name,
					value,
					value_int,
					locale
				)
				SELECT
					p.product_id,
					'year',
					p.year,
					p.year,
					'EN'
				FROM
					product AS p
				WHERE
					p.year != ''
		");

		$this->run("
			ALTER TABLE
				product
			DROP
				year
		");

		$this->run("
			INSERT INTO
				product_detail
				(
					product_id,
					name,
					value,
					value_int,
					locale
				)
				SELECT
					i.product_id,
					'season',
					i.season,
					i.season,
					'EN'
				FROM
					product_info AS i
				WHERE
					i.season != ''
		");

		$this->run("
			ALTER TABLE
				product_info
			DROP
				season
		");

		$this->run("
			INSERT INTO
				product_detail
				(
					product_id,
					name,
					value,
					value_int,
					locale
				)
				SELECT
					i.product_id,
					'fabric',
					i.fabric,
					i.fabric,
					'EN'
				FROM
					product_info AS i
				WHERE
					i.fabric != ''
		");

		$this->run("
			ALTER TABLE
				product_info
			DROP
				fabric
		");

		$this->run("
			INSERT INTO
				product_detail
				(
					product_id,
					name,
					value,
					value_int,
					locale
				)
				SELECT
					i.product_id,
					'features',
					i.features,
					i.features,
					'EN'
				FROM
					product_info AS i
				WHERE
					i.features != ''
		");

		$this->run("
			ALTER TABLE
				product_info
			DROP
				features
		");

		$this->run("
			INSERT INTO
				product_detail
				(
					product_id,
					name,
					value,
					value_int,
					locale
				)
				SELECT
					i.product_id,
					'care_instructions',
					i.care_instructions,
					i.care_instructions,
					'EN'
				FROM
					product_info AS i
				WHERE
					i.care_instructions != ''
		");

		$this->run("
			ALTER TABLE
				product_info
			DROP
				care_instructions
		");

		$this->run("
			INSERT INTO
				product_detail
				(
					product_id,
					name,
					value,
					value_int,
					locale
				)
				SELECT
					i.product_id,
					'sizing',
					i.sizing,
					i.sizing,
					'EN'
				FROM
					product_info AS i
				WHERE
					i.sizing != ''
		");

		$this->run("
			ALTER TABLE
				product_info
			DROP
				sizing
		");

		$this->run("
			ALTER TABLE
				product
			MODIFY
				type VARCHAR(255) DEFAULT 'basic'
			AFTER
				deleted_by
		");
	}

	public function down()
	{
		$this->run("
			ALTER TABLE
				product_info
			ADD
				sizing VARCHAR(255)
			AFTER
				short_description
		");

		$this->run("
			INSERT INTO
				product_info
				(
					product_id
				)
				SELECT
					d.product_id
				FROM
					product_detail AS d
				JOIN
					product_info
				USING
					(product_id)
				WHERE
					d.name = 'sizing'
			ON DUPLICATE KEY UPDATE
				sizing = d.value
		");

		$this->run("
			ALTER TABLE
				product_info
			ADD
				care_instructions VARCHAR(255)
			AFTER
				display_name
		");

		$this->run("
			INSERT INTO
				product_info
				(
					product_id
				)
				SELECT
					d.product_id
				FROM
					product_detail AS d
				JOIN
					product_info
				USING
					(product_id)
				WHERE
					d.name = 'care_instructions'
			ON DUPLICATE KEY UPDATE
				care_instructions = d.value
		");

		$this->run("
			ALTER TABLE
				product_info
			ADD
				features VARCHAR(255)
			AFTER
				display_name
		");

		$this->run("
			INSERT INTO
				product_info
				(
					product_id
				)
				SELECT
					d.product_id
				FROM
					product_detail AS d
				JOIN
					product_info
				USING
					(product_id)
				WHERE
					d.name = 'features'
			ON DUPLICATE KEY UPDATE
				features = d.value
		");

		$this->run("
			ALTER TABLE
				product_info
			ADD
				fabric VARCHAR(255)
			AFTER
				display_name
		");

		$this->run("
			INSERT INTO
				product_info
				(
					product_id
				)
				SELECT
					d.product_id
				FROM
					product_detail AS d
				JOIN
					product_info
				USING
					(product_id)
				WHERE
					d.name = 'fabric'
			ON DUPLICATE KEY UPDATE
				fabric = d.value
		");

		$this->run("
			ALTER TABLE
				product_info
			ADD
				season VARCHAR(255)
			AFTER
				display_name
		");

		$this->run("
			INSERT INTO
				product_info
				(
					product_id
				)
				SELECT
					d.product_id
				FROM
					product_detail AS d
				JOIN
					product_info
				USING
					(product_id)
				WHERE
					d.name = 'season'
			ON DUPLICATE KEY UPDATE
				season = d.value
		");

		$this->run("
			ALTER TABLE
				product
			ADD
				year YEAR(4)
			AFTER
				product_id
		");

		$this->run("
			INSERT INTO
				product
				(
					product_id
				)
				SELECT
					d.product_id
				FROM
					product_detail AS d
				JOIN
					product
				USING
					(product_id)
				WHERE
					d.name = 'year'
			ON DUPLICATE KEY UPDATE
				year = d.value
		");

		$this->run("
			ALTER TABLE
				product
			DROP
				type
		");

		$this->run("
			DROP TABLE
				product_detail;
		");
	}
}