<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\DB\Query as DBQuery;

class Searcher {
	protected $_dbQuery;
	protected $_requirements;

	protected $_searchParams = array();
	protected $_queryString;

	protected $_minTermLength;

	/**
	 * Whether the search has already been run or not.
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
	 * @param  int      $minTermLength minimal term length
	 * @return Searcher $this for chainability
	 */
	public function setMinTermLength($minTermLength)
	{
		$this->_minTermLength = (int) $minTermLength;

		return $this;
	}

	/**
	 * Gets minimum term length
	 * @return int minimal term length
	 */
	public function getMinTermLength()
	{
		return $this->_minTermLength;
	}

	/**
	 * Sets requirement for field, overrides existing requirement for the field.
	 * @param  string          $field The field the requirement is added to
	 * @param  string          $term  The term searched for
	 * @throws \LogicException        If search has already been run
	 * @return Searcher               $this for chainability
	 */
	public function setRequirement($field, $term)
	{
		if($this->_run) {
			throw new \LogicException('Cannot set requirements after query has already been run.');
		}
		if(self::MIN_TERM_LENGTH)
		$this->_requirements[$field] = $term;

		return $this;
	}

	/**
	 * Returns an array of products that match all requirements.
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
	 * @throws \LogixException If no requirements have been set yet
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
				$wheres[] = '(LOWER(' . $field . ') LIKE :' . $field . ' OR LOWER(product_info.short_description) LIKE :' . $field .')';
			} else {
				$field = 'product.' . $field;
				$wheres[] = 'LOWER(' . $field . ') LIKE :' . $field;
			}

			$this->_searchParams[$field] = '%' . $term . '%';
		}

		$where = join($wheres, ' AND ');

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