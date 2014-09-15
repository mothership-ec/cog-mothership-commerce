<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product\Type\FieldCrawler;

use Symfony\Component\Validator\Constraints;

class Validate
{
	private $_headingKeys = [];

	private $_validRows   = [];
	private $_invalidRows = [];

	private $_required;

	public function __construct(HeadingKeys $headingKeys, FieldCrawler $fieldCrawler)
	{
		$this->_headingKeys  = $headingKeys;
		$this->_required     = $headingKeys->getRequired();
		$this->_fieldCrawler = $fieldCrawler;
	}

	public function validateRow(array $row)
	{
		foreach ($row as $key => $column) {
			if (
				(in_array($key, $this->_required) && empty($column)) ||
				!$this->_validateWithConstraints($column)
			) {
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

	private function _validateWithConstraints(array $row)
	{
		foreach ($row as $key => $column) {
			$key         = $this->_headingKeys->getKey($key);
			$constraints = $this->_fieldCrawler->getConstraints($key);

			foreach ($constraints as $constraint) {
				if (!$constraint instanceof Constraints\NotBlank) {
					$validator = new $constraint->validatedBy();

					$validator->validate($column, $constraint);
				}
			}

		}
	}
}