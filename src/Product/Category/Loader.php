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

	/**
	 * @var bool
	 */
	protected $_includeDeleted = false;

	public function __construct(Query $query)
	{
		$this->_query	= $query;
	}

	public function includeDeleted($includeDeleted = true)
	{
		if (!is_bool($includeDeleted)) {
			throw new \InvalidArgumentException('$includeDeleted must be a boolean');
		}

		$this->_includeDeleted = $includeDeleted;
	}

	public function getCategories($includeDeleted = null)
	{
		if (null !== $includeDeleted) {
			$this->includeDeleted($includeDeleted);
		}

		$result	= $this->_query->run("
			SELECT DISTINCT
				category
			FROM
				product
			WHERE
				category IS NOT NULL
			AND
				category != ''
			" . (!$this->_includeDeleted ? " AND deleted_at IS NULL " : "") . "
		");

		return $result->flatten();
	}
}