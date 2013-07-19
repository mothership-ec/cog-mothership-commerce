<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Mothership\Commerce\Product\Unit\LoaderInterface;
use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Localisation\Locale;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;


class Loader implements LoaderInterface
{
	protected $_query;
	protected $_locale;

	protected $_loadInvisible  = true;
	protected $_loadOutOfStock = false;

	public function __construct(Query $query, Locale $locale)
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

	public function includeInvisible($bool)
	{
		$this->_loadInvisible = $bool;
	}

	public function includeOutOfStock($bool)
	{
		$this->_loadOutOfStock = $bool;
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
				product_unit_stock.unit_id     AS id,
				product_unit_stock.stock       AS stock,
				product_unit_stock.location_id AS locationID
			FROM
				product_unit_stock
			WHERE
				product_unit_stock.unit_id IN (?ij)
		', 	array(
				(array) $unitIDs,
			)
		);

		$prices = $this->_query->run(
			'SELECT
				product_unit.unit_id       AS id,
				product_price.type          AS type,
				product_price.currency_id   AS currencyID,
				IFNULL(
					product_unit_price.price, product_price.price
				)     							 AS price
			FROM
				product_price
			JOIN
				product_unit ON (product_price.product_id = product_unit.product_id)
			LEFT JOIN
				product_unit_price ON (product_unit.unit_id = product_unit_price.unit_id AND product_price.type = product_unit_price.type)
			WHERE
				product_unit.unit_id IN (?ij)
		', 	array(
				(array) $unitIDs,
			)
		);

		$options = $this->_query->run(
			'SELECT
				product_unit_option.unit_id AS id,
				product_unit_option.option_name AS name,
				product_unit_option.option_value AS value
			FROM
				product_unit_option
			WHERE
				unit_id IN (?ij)',
			array(
				(array) $unitIDs,
			)
		);

		$bind = $result->bindTo('Message\\Mothership\\Commerce\\Product\\Unit\\Unit', array($this->_locale, $product->priceTypes));

		// Set the unit_id as the array key
		$units = array();
		foreach ($bind as $unit) {
			$units[$unit->id] = $unit;
		}

		foreach ($units as $key => $data) {

			if (!$this->_loadInvisible && !$data->visible) {
				unset($units[$key]);
				continue;
			}

			foreach ($stock as $values) {
				if ($values->id == $data->id) {
					$units[$key]->stock[$values->locationID] = $values->stock;
				}
			}

			foreach ($options as $option) {
				if ($option->id == $data->id) {
					$units[$key]->options[$option->name] = $option->value;
				}
			}

			if (!$this->_loadOutOfStock && array_sum($units[$key]->stock) == 0) {
				unset($units[$key]);
				continue;
			}

			foreach ($prices as $price) {
				if ($price->id == $data->id) {
					$units[$key]->price[$price->type]->setPrice($price->currencyID, $price->price);
				}
			}
		}

		return count($units) == 1 && !$this->_returnArray ? array_shift($units) : $units;
	}
}