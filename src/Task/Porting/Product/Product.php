<?php

namespace Message\Mothership\Commerce\Task\Porting\Product;

use Message\Mothership\Commerce\Task\Porting\Porting;

class Product extends Porting
{

	public function process()
	{
		$this->portProductData();
		$this->portPrices();
		$this->portExportData();
		$this->portTags();
	}

    public function portProductData()
    {
        $uwOld = $this->getFromConnection();
		$uwNew = $this->getToCOnnection();

		$new = new \Message\Cog\DB\Transaction($uwNew);
		$old = new \Message\Cog\DB\Query($uwOld);

		$sql = 'SELECT
					catalogue.catalogue_id,
					catalogue.product_id,
					catalogue.product_year AS year,
					UNIX_TIMESTAMP() AS created_at,
					NULL AS created_by,
					NULL AS udpated_at,
					NULL AS updated_by,
					IF (catalogue.date_deleted IS NOT NULL, UNIX_TIMESTAMP(catalogue.date_deleted), NULL) AS deleted_at,
					NULL AS deleted_by,
					brand_name AS brand,
					catalogue_product.product_name AS name,
					order_tax.tax_rate,
					catalogue_product.supplier_ref,
					catalogue_product.weight AS weight_grams,
					category_name AS category
				FROM
					catalogue
				JOIN
					catalogue_product USING (product_id)
				JOIN
					brand_info USING (brand_id)
				JOIN
					category_info USING (category_id)
				JOIN
					order_tax USING (tax_code)';

		$result = $old->run($sql);
		$new->add('TRUNCATE product');
		$new->add('TRUNCATE product_info');
		$output= '';
		foreach($result as $row) {
			$new->add('
				INSERT INTO
					product
				(
					product_id,
					year,
					created_at,
					created_by,
					updated_at,
					updated_by,
					deleted_at,
					deleted_by,
					brand,
					name,
					tax_rate,
					supplier_ref,
					weight_grams,
					category
				)
				VALUES
				(
					:product_id?,
					:year?,
					:created_at?,
					:created_by?,
					:updated_at?,
					:updated_by?,
					:deleted_at?,
					:deleted_by?,
					:brand?,
					:name?,
					:tax_rate?,
					:supplier_ref?,
					:weight_grams?,
					:category?
				)', (array) $row);
		}

		$sql = 'SELECT
					catalogue_id      AS product_id,
					\'en_GB\'         AS locale,
					display_name      AS display_name,
					season            AS season,
					description       AS description,
					fabric            AS fabric,
					care_instructions AS care_instructions,
					short_description AS short_description,
					sizing            AS sizing,
					notes             AS notes
				FROM
					catalogue_info';

		$result = $old->run($sql);

		foreach($result as $row) {
			$new->add('INSERT INTO
				product_info
				(
					product_id,
					locale,
					display_name,
					season,
					description,
					fabric,
					care_instructions,
					short_description,
					sizing,
					notes
				)
				VALUES
				(
					:product_id?,
					:locale?,
					:display_name?,
					:season?,
					:description?,
					:fabric?,
					:care_instructions?,
					:short_description?,
					:sizing?,
					:notes?
				)', (array) $row);
		}

		if ($new->commit()) {
        	$this->writeln('<info>Successfully ported products</info>');
		}
    }

    public function portPrices()
    {
        $uwOld = $this->getFromConnection();
		$uwNew = $this->getToCOnnection();

		$new = new \Message\Cog\DB\Transaction($uwNew);
		$old = new \Message\Cog\DB\Query($uwOld);

		$new->add('TRUNCATE product_price');
		$new->add('TRUNCATE product_unit_price');

		$sql = 'SELECT
					catalogue_id AS product_id,
					price,
					rrp,
					cost_price,
					sale_price,
					wholesale_price,
					\'GBP\' AS currency_id,
					\'en_GB\' AS locale
				FROM
					catalogue_info';

		$result = $old->run($sql);

		$formatedData = array();
		foreach ($result as $data) {
			$formatedData[$data->product_id]['retail'] = array(
				'price' => $data->price,
				'currency_id' => $data->currency_id,
				'locale' => $data->locale,
			);
			$formatedData[$data->product_id]['rrp'] = array(
				'price' => $data->rrp,
				'currency_id' => $data->currency_id,
				'locale' => $data->locale,
			);
			$formatedData[$data->product_id]['cost'] = array(
				'price' => $data->cost_price,
				'currency_id' => $data->currency_id,
				'locale' => $data->locale,
			);
		}

		$output= '';
		foreach($formatedData as $productID => $data) {
			foreach ($data as $type => $values) {
				$new->add('
					INSERT INTO
						product_price
					(
						product_id,
						`type`,
						`price`,
						currency_id,
						locale
					)
				VALUES
					(
						:product_id?,
						:type?,
						:price?,
						:currency_id?,
						:locale?
					);', array(
						'product_id'  => $productID,
						'type'        => $type,
						'price'       => $values['price'],
						'currency_id' => $values['currency_id'],
						'locale'      => $values['locale'],
				));

			}
		}

		$sql = 'SELECT
					unit_id,
					price,
					rrp,
					cost_price,
					\'GBP\' AS currency_id,
					\'en_GB\' AS locale
				FROM
					catalogue_unit
				LEFT JOIN catalogue_unit_price USING (unit_id)
				LEFT JOIN catalogue_unit_rrp USING (unit_id)
				LEFT JOIN catalogue_unit_cost_price USING (unit_id)';

		$result = $old->run($sql);

		$formatedData = array();
		foreach ($result as $data) {
			$formatedData[$data->unit_id]['retail'] = array(
				'price' => $data->price,
				'currency_id' => $data->currency_id,
				'locale' => $data->locale,
			);
			$formatedData[$data->unit_id]['rrp'] = array(
				'price' => $data->rrp,
				'currency_id' => $data->currency_id,
				'locale' => $data->locale,
			);
			$formatedData[$data->unit_id]['cost'] = array(
				'price' => $data->cost_price,
				'currency_id' => $data->currency_id,
				'locale' => $data->locale,
			);
		}

		$output= '';
		foreach($formatedData as $unitID => $data) {
			foreach ($data as $type => $values) {
				if (is_null($values['price'])) {
					continue;
				}
				$new->add('
					INSERT INTO
						product_unit_price
					(
						unit_id,
						`type`,
						`price`,
						currency_id,
						locale
					)
				VALUES
					(
						:unit_id?,
						:type?,
						:price?,
						:currency_id?,
						:locale?
					);', array(
						'unit_id'  	  => $unitID,
						'type'        => $type,
						'price'       => $values['price'],
						'currency_id' => $values['currency_id'],
						'locale'      => $values['locale'],
				));

			}
		}

		if ($new->commit()) {
        	$this->writeln('<info>Successfully ported product prices</info>');
		}
    }

    public function portExportData()
    {
        $uwOld = $this->getFromConnection();
		$uwNew = $this->getToCOnnection();

		$new = new \Message\Cog\DB\Transaction($uwNew);
		$old = new \Message\Cog\DB\Query($uwOld);

		$sql = 'SELECT
					catalogue_id AS product_id,
					\'en_GB\' AS locale,
					export_value,
					export_description,
					export_manufacture_country_id
				FROM
					catalogue_export';

		$result = $old->run($sql);
		$new->add('TRUNCATE product_export');
		$output= '';
		foreach($result as $row) {
			$new->add('
				INSERT INTO
					product_export
				(
					product_id,
					locale,
					export_value,
					export_description,
					export_manufacture_country_id
				)
				VALUES
				(
					:product_id?,
					:locale?,
					:export_value?,
					:export_description?,
					:export_manufacture_country_id?
				)', (array) $row);
		}

		if ($new->commit()) {
        	$this->writeln('<info>Successfully ported product export</info>');
		}
    }

	public function portTags()
    {
        $uwOld = $this->getFromConnection();
		$uwNew = $this->getToCOnnection();

		$new = new \Message\Cog\DB\Transaction($uwNew);
		$old = new \Message\Cog\DB\Query($uwOld);

		$sql = 'SELECT
					catalogue_id AS product_id,
					range_name AS name
				FROM
					catalogue_range
				JOIN val_range USING (range_id)
				WHERE
					range_name != \'\'';

		$result = $old->run($sql);
		$new->add('TRUNCATE product_tag');
		$output= '';
		foreach($result as $row) {
			$new->add('
				INSERT INTO
					product_tag
				(
					product_id,
					name
				)
				VALUES
				(
					:product_id?,
					:name?
				)', (array) $row);
		}

		if ($new->commit()) {
        	$this->writeln('<info>Successfully ported product tags</info>');
		}
    }
}