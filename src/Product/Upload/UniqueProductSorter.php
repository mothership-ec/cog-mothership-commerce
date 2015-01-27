<?php

namespace Message\Mothership\Commerce\Product\Upload;

class UniqueProductSorter
{
	private $_sortedRows = [];

	public function __construct(HeadingKeys $headingKeys)
	{
		$this->_headingKeys = $headingKeys;
	}

	public function sort(array $validRows)
	{
		foreach ($validRows as $row) {
			$hash = $this->_getHash($row);
			if  (!array_key_exists($hash, $this->_sortedRows)) {
				$this->_sortedRows[$hash] = [];
			}
			$this->_sortedRows[$hash][] = $row;
		}

		return $this->_sortedRows;
	}

	private function _getHash(array $row)
	{
		if (!$this->_isValid($row)) {
			throw new Exception\UploadException('Row must have keys for name, category and brand');
		}

		return md5(
			$row[$this->_headingKeys->getKey('name')] .
			$row[$this->_headingKeys->getKey('category')] .
			$row[$this->_headingKeys->getKey('brand')]
		);
	}

	private function _isValid(array $row)
	{
		return (
			array_key_exists($this->_headingKeys->getKey('name'), $row) &&
			array_key_exists($this->_headingKeys->getKey('category'), $row) &&
			array_key_exists($this->_headingKeys->getKey('brand'), $row)
		);
	}
}