<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\DB\Query as DBQuery;

/**
 * Class loading products matching certain requirements from the database.
 */
class Searcher {
	protected $_dbQuery;
	protected $_requirements;

	protected $_searchParams = array();
	protected $_queryString;

	protected $_minTermLength;

	/**
	 * Whether the search has already been run or not.
	 *
	 * @var boolean
	 */
	protected $_run = false;


	public function __construct(DBQuery $dbQuery, Loader $loader, $minTermLength = 0)
	{
		$this->_dbQuery       = $dbQuery;
		$this->_loader        = $loader;
		$this->_minTermLength = (int) $minTermLength;
	}

	/**
	 * Sets minimum term length
	 *
	 * @param  int      $minTermLength minimal term length
	 *
	 * @return Searcher $this for chainability
	 */
	public function setMinTermLength($minTermLength)
	{
		$this->_minTermLength = (int) $minTermLength;

		return $this;
	}

	/**
	 * Gets minimum term length
	 *
	 * @return int minimal term length
	 */
	public function getMinTermLength()
	{
		return $this->_minTermLength;
	}

	/**
	 * Sets requirement and overrides existing requirement for the field.
	 *
	 * @param  string                    $field The field the requirement is added to
	 * @param  string                    $term  The term searched for
	 * @throws \LogicException                  If search has already been run
	 * @throws \InvalidArgumentException        If term is shorter than $_minTermLength
	 *
	 * @return Searcher                         $this for chainability
	 */
	public function setRequirement($field, $term)
	{
		if ($this->_run) {
			throw new \LogicException('Cannot set requirements after query has already been run.');
		}

		if ($this->getMinTermLength() > strlen($term)) {
			throw new \InvalidArgumentException(
				sprintf(
					'Search term has to be at least %s characters long.',
					$this->getMinTermLength()
				)
			);
		}

		$this->_requirements[$field] = $term;

		return $this;
	}

	/**
	 * Returns all requirements set on this searcher
	 *
	 * @return array all requirements set
	 */
	public function getRequirements()
	{
		return $this->_requirements;
	}

	/**
	 * Returns an array of products that match all requirements.
	 *
	 * @return array[Product] Array of products matching $_requirements
	 */
	public function run()
	{
		$this->_run = true;
		$this->_buildQuery();

		$results = $this->_dbQuery->run($this->_queryString, $this->_searchParams);

		return $this->_loader->getByID($results->flatten());
	}

	/**
	 * Sets $_queryString and $_searchParams
	 *
	 * @throws \LogicException If no requirements have been set yet
	 */
	protected function _buildQuery()
	{
		if ($this->_queryString && $this->_searchParams) {
			return;
		}

		if (0 === count($this->_requirements)) {
			throw new \LogicException("At least one requirement has to be added before you can run the search.");
		}

		$wheres = [];

		// Loop terms and build query against each one.
		// Terms are lowered to ensure they are case-insensitive.
		foreach ($this->_requirements as $field => $term) {
			$term = strtolower($term);

			if ('description' == $field) {
				$field = 'product_info.' . $field;
				$wheres[] = '(LOWER(' . $field . ') LIKE :' . $field . '?s OR LOWER(product_info.short_description) LIKE :' . $field .'?s)';
			} else {
				$field = 'product.' . $field;
				$wheres[] = 'LOWER(' . $field . ') LIKE :' . $field . '?s';
			}

			// replace '*' with '%' and add '%' in end and beginning
			$this->_searchParams[$field]             = '%' . str_replace('*', '%', $term) . '%';
		}

		$where = implode(' AND ', $wheres);

		$this->_queryString = '
			SELECT
				product.product_id
			FROM
				product
			JOIN
				product_info ON product.product_id = product_info.product_id
			WHERE
				' . $where . '
		';
	}
}