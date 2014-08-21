<?php

namespace Message\Mothership\Commerce\Product\Upload\Csv;

class CsvBuilder
{
	/**
	 * @var Columns
	 */
	protected $_columns;

	public function __construct(Columns $columns)
	{
		$this->_columns = $columns;
	}
}