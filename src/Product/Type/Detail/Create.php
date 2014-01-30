<?php

namespace Message\Mothership\Commerce\Product\Type\Detail;

use Message\Cog\DB\Query;

class Create
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query	= $query;
	}

	public function create(Detail $detail)
	{
		$this->_query->run("
			INSERT INTO
				product_detail
				(
					product_id,
					name,
					value
				)
				VALUES
				(
					:productID?i,
					:name?s,
					:value?s
				);
		", array(
			'productID'	=> $detail->productID,
			'name'		=> $detail->name,
			'value'		=> $detail->value,
		));
	}
}