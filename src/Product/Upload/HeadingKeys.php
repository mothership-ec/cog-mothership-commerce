<?php

namespace Message\Mothership\Commerce\Product\Upload;

class HeadingKeys
{
	const VAR_NAME_PREFIX = 'var_name.name';
	const VAR_VAL_PREFIX  = 'var_val.name';
	const NUM_VARIANTS    = 3;

	private $_columns;

	private $_required = [
		'name',
		'category',
		'price'
	];

	private $_floats = [
		'price',
		'rrp',
		'cost',
		'taxRate',
	];

	public function __construct(array $headingColumns)
	{
		$this->_columns = $headingColumns;
		$this->_setRequiredKeys();
		$this->_setFloatKeys();
	}

	public function getKey($key)
	{
		if (!array_key_exists($key, $this->_columns)) {
			throw new \LogicException('Column `' . $key . '` does not exist!');
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

	private function _setRequiredKeys()
	{
		foreach ($this->_required as $key => $value) {
			$this->_required[$key] = $this->getKey($value);
		}
	}

	private function _setFloatKeys()
	{
		foreach ($this->_floats as $key => $value) {
			$this->_floats[$key] = $this->getKey($value);
		}
	}
}