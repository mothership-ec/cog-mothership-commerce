<?php

namespace Message\Mothership\Commerce\Product\Upload;

class HeadingKeys
{
	const VAR_NAME_PREFIX = 'var_name.name';
	const VAR_VAL_PREFIX  = 'var_val.name';
	const NUM_VARIANTS    = 3;

	private $_columns;

	public function __construct(array $headingColumns)
	{
		$this->_columns = $headingColumns;
	}

	public function getKey($key)
	{
		if (!array_key_exists($key, $this->_columns)) {
			throw new \LogicException('Column `' . $key . '` does not exist!');
		}

		return $this->_columns[$key];
	}
}