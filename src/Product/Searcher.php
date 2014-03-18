<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\DB\Query as DBQuery;

class Searcher {

	const MIN_REQUIREMENTS = 1;

	protected $_query;
	protected $_requirements;
	protected $_run = false;


	public function __construct(DBQuery $query, Loader $loader)
	{
		$this->_query  = $query;
		$this->_loader = $loader;
	}

	/**
	 * Sets requirement for field, overrides existing requirement for the field.
	 * @param  string   $field The field the requirement is added to
	 * @param  string   $term  The term searched for
	 * @return Searcher        $this for chainability
	 */
	public function setRequirement($field, $term)
	{
		if($this->_run) {
			throw new \LogicException('Cannot set requirements after query has already been run.');
		}
		$this->_requirements[$field] = $term;

		return $this;
	}

	/**
	 * Returns an array of products that match all requirements.
	 *
	 * @return array[Product] Array of products matching $_requirements
	 */
	public function run()
	{
		$this->_run = true;

		$results = $this->_query->run($this->_buildQuery);

		return $this->_loader->getByID($results->flatten());
	}

	/**
	 * Builds SQL Query from requirements.
	 * @throws \LogixException If less than MIN_REQUIREMENTS have been set
	 * @return array           Array of query and search parameters, to be used
	 *                         in $_query
	 */
	protected function _buildQuery()
	{
		if (count($this->_requirements) < self::MIN_REQUIREMENTS) {
			throw new \LogicException("At least %s requirement(s) have to be added before you can run the search.");
		}

		$wheres = [];
		$searchParams = array();

		// Loop terms and build query against each one.
		// Terms are lowered to ensure they are case-insensitive.
		foreach ($this->_requirements as $field => $term) {
				$term = strtolower($term);

				$wheres[] = 'LOWER(' . $field . ') LIKE :' . $field;

				$searchParams[$field] = '%' . $term . '%';
		}

		$query = '(' . join($wheres, ' AND ') . ')';

		$query = '
			SELECT
				product_id,
			FROM
				products
			WHERE
				' . $query . '
		';

		de($query);

		return array($query, $searchParams);
	}
}