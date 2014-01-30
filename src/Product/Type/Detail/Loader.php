<?php

namespace Message\Mothership\Commerce\Product\Type\Detail;

use Message\Cog\DB\Query;
use Message\Mothership\Commerce\Product\Product;

class Collection
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query	= $	query;
	}

	public function getDetails(Product $product)
	{
		$result =	$this->_query->run("
			SELECT
				`name`,
				`value`
			FROM
				product_detail
			WHERE
				id	= :productID?i
		", array(
			'productID'	=> $product->id,
		));

		$result		= $result->bindTo('Message\\Mothership\\Commerce\\Product\\Type\\Detail\\Detail');
		$collection	= new Collection($result);

		return $collection;

	}
}