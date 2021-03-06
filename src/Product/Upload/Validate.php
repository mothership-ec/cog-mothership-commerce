<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product\Type\FieldCrawler;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Validation;

class Validate
{
	private $_headingKeys = [];

	private $_validRows     = [];
	private $_invalidRows   = [];
	private $_dependantCols = [];
	private $_required;

	public function __construct(HeadingKeys $headingKeys, FieldCrawler $fieldCrawler)
	{
		$this->_headingKeys   = $headingKeys;
		$this->_required      = $headingKeys->getRequired();
		$this->_dependantCols = $headingKeys->getColumnDependencies();
		$this->_fieldCrawler  = $fieldCrawler;
	}

	public function validateRow(array $row)
	{
		foreach ($row as $key => $column) {
			if (in_array($key, $this->_required) && empty($column)) {
				$this->_invalidRows[] = $row;

				return false;
			}

			if (isset($this->_dependantCols[$key]) && !empty($row[$key])) {
				foreach ($this->_dependantCols[$key] as $dep) {
					if (empty($row[$dep])) {
						$this->_invalidRows[] = $row;

						return false;
					}
				}
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

	public function invalidateRow(array $row)
	{
		$key = array_search($row, $this->_validRows);

		if ($key) {
			unset($this->_validRows[$key]);
		}

		$this->_invalidRows[] = $row;
	}

	private function _clear()
	{
		$this->_validRows   = [];
		$this->_invalidRows = [];
	}
}