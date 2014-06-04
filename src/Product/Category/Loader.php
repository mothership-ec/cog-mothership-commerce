<?php

namespace Message\Mothership\Commerce\Product\Category;

use Message\Cog\DB\Query;

/**
 * Simple class for loading category options out of the database
 */
class Loader
{
	/**
	 * @var \Message\Cog\DB\Query
	 */
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query	= $query;
	}

	public function getCategories($includeDeleted = false)
	{
		$result	= $this->_query->run("
			SELECT DISTINCT
				category
			FROM
				product
			WHERE
				category IS NOT NULL
			AND
				category != ''
			" . (!$includeDeleted ? " AND deleted_at IS NULL " : "") . "
		");

		return $result->flatten();
	}
}