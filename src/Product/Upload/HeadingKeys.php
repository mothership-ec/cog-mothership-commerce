<?php

namespace Message\Mothership\Commerce\Product\Upload;

class HeadingKeys
{
	const VAR_NAME_PREFIX = 'var_name.name';
	const VAR_VAL_PREFIX  = 'var_val.name';
	const NUM_VARIANTS    = 3;

	private $_columns;

	private $_currencies;

	private $_required = [
		'name',
		'category',
	];

	private $_requiredPrices = [
		'retail',
	];

	private $_floats = [
	];

	private $_prices = [
		'cost',
		'retail',
		'rrp',
	];

	/**
	 * @var array The dependent columns, [column => dependencies]
	 */
	private $_dependantCols = [];

	public function __construct(array $headingColumns, array $currencies)
	{
		$this->_columns = $headingColumns;
		$this->_currencies = $currencies;
		$this->_setRequiredKeys();
		$this->_setFloatKeys();
	}

	public function getKey($key)
	{
		if (!array_key_exists($key, $this->_columns)) {
			return $this->_getFlippedKey($key);
		}

		return $this->_columns[$key];
	}

	public function getRequired()
	{
		return $this->_required;
	}

	public function setRequired(array $required)
	{
		$this->_required = $required;

		return $this;
	}

	public function getFloats()
	{
		return $this->_floats;
	}

	public function setFloats(array $floats)
	{
		$this->_floats = $floats;

		return $this;
	}

	/**
	 * Gets column dependencies
	 * 
	 * @return array The dependent columns, column => dependencies
	 */
	public function getColumnDependencies()
	{
		return $this->_dependantCols;
	}

	/**
	 * Sets column dependencies
	 * 
	 * @param array $dependencies The dependent columns, column => dependencies
	 */
	public function setColumnDependencies(array $dependencies)
	{
		$this->_dependantCols = $dependencies;
	}

	/**
	 * Adds a column dependency
	 * 
	 * @param string $column     The column name
	 * @param string $dependency The dependency to add
	 */
	public function addColumnDependency($column, $dependency)
	{
		if (!isset($this->_dependantCols[$column])) {
			$this->_dependantCols[$column] = [];
		}

		$this->_dependantCols[$column][] = $dependency;
	}

	private function _getFlippedKey($key)
	{
		$flipped = array_flip($this->_columns);

		if (!array_key_exists($key, $flipped)) {
			throw new \LogicException('Column `' . $key . '` does not exist!');
		}

		return $flipped[$key];
	}

	private function _setRequiredKeys()
	{
		foreach ($this->_required as $key => $value) {
			$this->_required[$key] = $this->getKey($value);
		}

		foreach ($this->_requiredPrices as $price) {
			foreach ($this->_currencies as $currency) {
				$this->_required[] = $this->getKey($price . '.' . $currency);
			}
		}
	}

	private function _setFloatKeys()
	{
		foreach ($this->_floats as $key => $value) {
			$this->_floats[$key] = $this->getKey($value);
		}

		foreach ($this->_prices as $price) {
			foreach ($this->_currencies as $currency) {
				$this->_floats[] = $this->getKey($price . '.' . $currency);
			}
		}
	}
}