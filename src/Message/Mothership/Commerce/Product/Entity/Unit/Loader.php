<?php

namespace Message\Mothership\Commerce\Product\Entity\Unit;

use Message\Mothership\Commerce\Product\Entity\LoaderInterface;
use Message\Mothership\Commerce\Product\Product;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;

class Loader implements LoaderInterface
{
	protected $_query;
	protected $_locale;

	public function __construct(Query $query, Locale $locale = null)
	{
		$this->_query = $query;
		$this->_locale = $locale;
	}

	public function getByProduct(Product $product)
	{
		$result = $this->_query->run('
			SELECT
				unit_id
			FROM
				product_unit
			WHERE
				product_id = ?i
		', 	array(
				$product->id
			)
		);

		return count($result) ? $this->_load($result->flatten(), $product) : false;
	}

	protected function _load($unitIDs, Product $product)
	{

		$result = $this->_query->run(
			'SELECT
				product_unit.unit_id       AS id,
				product_unit.weight_grams  AS weightGrams,
				product_unit.sku           AS sku,
				product_unit.barcode       AS barcode,
				product_unit.visible       AS visible
			FROM
				product_unit
			WHERE
				product_unit.unit_id IN (?ij)
			GROUP BY
				product_unit.unit_id',
			array(
				(array) $unitIDs,
			)
		);

		$stock = $this->_query->run(
			'SELECT
				product_unit_stock.unit_id AS id,
				product_unit_stock.stock,
				product_unit_stock.location_id AS locationID
			FROM
				product_unit_stock
			WHERE
				product_unit_stock.unit_id IN (?ij)
		', array(
			(array) $unitIDs,
		));

		$prices = $this->_query->run(
			'SELECT
				product_unit_price.unit_id     AS id,
				product_unit_price.type        AS type,
				product_unit_price.currency_id AS currencyID,
				product_unit_price.price       AS price
			FROM
				product_unit_price
			WHERE
				product_unit_price.unit_id IN (?ij)
		', array(
			(array) $unitIDs,
		));

		$units = $result->bindTo('Message\\Mothership\\Commerce\\Product\\Entity\\Unit\\Unit');

		foreach ($units as $key => $data) {

			foreach ($stock as $values) {
				if ($values->id == $data->id) {
					$units[$key]->stock[$values->locationID] = $values->stock;
				}
			}

			foreach ($prices as $price) {
				if ($price->id == $data->id) {
					$units[$key]->price[$price->currencyID][$price->type] = $price->price ?: $product->price[$type];
				}
			}

		}

		return count($units) == 1 && !$this->_returnArray ? array_shift($units) : $units;
	}
}