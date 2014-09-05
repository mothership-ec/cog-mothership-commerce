<?php

namespace Message\Mothership\Commerce\Product\Upload;

class Validate
{
	private $_required = [];
	private $_validRows = [];
	private $_invalidRows = [];

	public function __construct(HeadingBuilder $headingBuilder)
	{
		$this->_required = $headingBuilder->getRequired();
	}

	public function validateRow(array $row)
	{
		foreach ($row as $key => $column) {
			if (in_array($key, $this->_required) && empty($column)) {
				$this->_invalidRows[] = $row;

				return false;
			}
		}

		$this->_validRows[] = $row;

		return true;
	}

	public function validate(array $rows)
	{
		$this->_clear();
		foreach ($rows as $row) {
			$this->validateRow($row);
		}

		return count($this->_invalidRows) <= 0;
	}

	public function getValidRows(array $rows = null)
	{
		if ($rows) {
			$this->validate($rows);
		}

		return $this->_validRows;
	}

	public function getInvalidRows(array $rows = null)
	{
		if ($rows) {
			$this->validate($rows);
		}

		return $this->_invalidRows;
	}

	private function _clear()
	{
		$this->_validRows   = [];
		$this->_invalidRows = [];
	}
}