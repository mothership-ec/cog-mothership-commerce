<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\DB\Query;


/**
 * Simple class for loading category options out of the database
 */
class CategoryLoader
{
	protected $_query;
	protected $_locale;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function getAll()
	{
		$result = $this->_query->run(
			'SELECT DISTINCT
				category
			FROM
				product'
		);

		return $result->flatten();
	}
}