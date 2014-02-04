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
					value_int INT(11),
					locale VARCHAR(50) DEFAULT VALUE 'EN',
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
					value_int,
					locale
				)
				SELECT
					p.product_id,
					'brand',
					p.brand,
					1,
					'EN'
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
					value_int,
					locale
				)
				SELECT
					p.product_id,
					'year',
					p.year,
					1
					'EN'
				FROM
					product AS p
		");

		$this->run("
			ALTER TABLE
				product
			DROP
				year
		");
	}

	public function down()
	{
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
			BEFORE
				name
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