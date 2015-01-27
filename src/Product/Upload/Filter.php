<?php

namespace Message\Mothership\Commerce\Product\Upload;

class Filter
{
	const PRICE_REGEX = '/[^\p{N}.]++/';

	private $_headingKeys;

	public function __construct(HeadingKeys $headingKeys)
	{
		$this->_headingKeys = $headingKeys;
	}

	public function filter(array &$rows)
	{
		foreach ($rows as $key => $row) {
			if ($this->_isEmpty($row)) {
				unset($rows[$key]);
			}
			else {
				$rows[$key] = $this->_filterFloats($row);
			}
		}

		$rows = array_values($rows);
	}

	public function _filterFloats(array $row)
	{
		foreach ($this->_headingKeys->getFloats() as $type) {
			if (!array_key_exists($type, $row)) {
				throw new \LogicException('`' . $type . '` does not exist in row');
			}
			if ($row[$type]) {
				$price = preg_replace(self::PRICE_REGEX, '', $row[$type]);
				$price = ($price === '') ? NULL : round((float) $price, 2);
				$row[$type] = $price;
			}
		}

		return $row;
	}

	private function _isEmpty(array $row)
	{
		foreach ($row as $column) {
			if (!empty($column)) {
				return false;
			}
		}

		return true;
	}
}