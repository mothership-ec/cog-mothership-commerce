<?php

namespace Message\Mothership\Commerce\Product\Entity\Unit;

use Message\Mothership\Commerce\Product\Entity\LoaderInterface;
use Message\Mothership\Commerce\Product\Product;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;

class Loader implements LoaderInterface
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
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
				product_unit.unit_id AS id,
				product_unit.weight_grams AS weightGrams,
				product_unit.stock AS stock,
				product_unit.price AS price,
				product_unit.sku AS sku,
				product_unit.barcode AS barcode,
				product_unit.visible AS visible,
			FROM
				product_unit
			WHERE
				unit_id IN (?ij)
			', array(
				(array) $unitIDs,
			));

		$products = $result->bindTo('Message\\Mothership\\Commerce\\Product\\Product', array($this->_entities));

		foreach ($result as $data) {
			de($data);
		}
	}
}