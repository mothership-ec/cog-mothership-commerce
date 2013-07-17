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

		return count($result) ? $this->_load($result->flatten()) : false;
	}

	protected function _load($unitIDs)
	{
		$result = $this->_query->run(
			'SELECT
				product_unit.unit_id       AS id,
				product_unit.weight_grams  AS weightGrams,
				product_unit_stock.stock   AS stock,
				IF(product_unit_price.type = "retail", product_unit_price.price, 0) AS retail,
				IF(product_unit_price.type = "rrp", product_unit_price.price, 0) AS retail,
				product_unit.sku           AS sku,
				product_unit.barcode       AS barcode,
				product_unit.visible       AS visible
			FROM
				product_unit
			JOIN
				product_unit_price ON (product_unit_price.unit_id = product_unit.product_id)
			LEFT JOIN
				product_unit_stock ON (product_unit.unit_id = product_unit_stock.unit_id)
			GROUP BY
				product_unit.unit_id
			WHERE
				unit_id IN (?ij)
			', array(
				'en_GB',
				(array) $unitIDs,
			));

		$products = $result->bindTo('Message\\Mothership\\Commerce\\Product\\Entity\\Unit');

		foreach ($result as $data) {
			de($data);
		}
	}
}