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
					product_id INT(11) NOT NULL,
					name VARCHAR(255) NOT NULL,
					value VARCHAR(255),
					locale VARCHAR(50) DEFAULT 'EN',
					data_type VARCHAR(255) DEFAULT 'text',
					PRIMARY KEY (product_id, name)
				);
		");

		$this->run("
			ALTER TABLE
				product
			ADD
				type VARCHAR(30)
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
					locale,
					data_type
				)
				SELECT
					p.product_id,
					'brand',
					p.brand,
					'EN',
					'text'
				FROM
					product AS p
		");

		$this->run("
			ALTER TABLE
				product
			DROP
				brand
		");

		$this->run("
			INSERT INTO
				product_detail
				(
					product_id,
					name,
					value,
					locale,
					data_type
				)
				SELECT
					p.product_id,
					'year',
					p.year,
					'EN',
					'text'
				FROM
					product AS p
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
					locale,
					data_type
				)
				SELECT
					p.product_id,
					'supplier_ref',
					p.supplier_ref,
					'EN',
					'text'
				FROM
					product AS p
		");

		$this->run("
			ALTER TABLE
				product
			DROP
				supplier_ref
		");

		$this->run("
			INSERT INTO
				product_detail
				(
					product_id,
					name,
					value,
					locale,
					data_type
				)
				SELECT
					i.product_id,
					'season',
					i.season,
					'EN',
					'text'
				FROM
					product_info AS i
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
					locale,
					data_type
				)
				SELECT
					i.product_id,
					'fabric',
					i.fabric,
					'EN',
					'text'
				FROM
					product_info AS i
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
					locale,
					data_type
				)
				SELECT
					i.product_id,
					'features',
					i.features,
					'EN',
					'text'
				FROM
					product_info AS i
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
					locale,
					data_type
				)
				SELECT
					i.product_id,
					'care_instructions',
					i.care_instructions,
					'EN',
					'text'
				FROM
					product_info AS i
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
					locale,
					data_type
				)
				SELECT
					i.product_id,
					'sizing',
					i.sizing,
					'EN',
					'text'
				FROM
					product_info AS i
		");

		$this->run("
			ALTER TABLE
				product_info
			DROP
				sizing
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
				supplier_ref VARCHAR(255)
			AFTER
				tax_strategy
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
					d.name = 'supplier_ref'
			ON DUPLICATE KEY UPDATE
				supplier_ref = d.value
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
			ADD
				brand VARCHAR(255)
			AFTER
				type
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
					d.name = 'brand'
			ON DUPLICATE KEY UPDATE
				brand = d.value
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