<?php

namespace Message\Mothership\Commerce\Product\Type\Detail;

use Message\Cog\DB\Query;
use Message\Mothership\Commerce\Product\Product;

class Loader
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query	= $query;
	}

	public function getDetails(Product $product)
	{
		$result =	$this->_query->run("
			SELECT
				product_id AS productID,
				name,
				value,
				value_int AS valueInt,
				locale
			FROM
				product_detail
			WHERE
				product_id	= :productID?i
		", array(
			'productID'	=> $product->id,
		));

		$result		= $result->bindTo('Message\\Mothership\\Commerce\\Product\\Type\\Detail\\Detail');
		$collection	= new Collection($result);

		return $collection;

	}
}