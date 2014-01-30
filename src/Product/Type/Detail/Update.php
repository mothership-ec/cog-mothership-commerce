<?php

namespace Message\Mothership\Commerce\Product\Type\Detail;

use Message\Cog\DB\Query;

class Update
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query	= $query;
	}

	public function update(Detail $detail)
	{
		$this->_query->run("
			UPDATE
				product_detail
			SET
				name		= :name?s,
				value		= :value?s
			WHERE
				product_id	= :productID?i
		", array(
			'productID'	=> $detail->productID,
			'name'		=> $detail->name,
			'value'		=> $detail->value,
		));
	}
}