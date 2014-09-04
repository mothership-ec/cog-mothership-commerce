<?php

namespace Message\Mothership\Commerce\Product\Upload\Csv;

class Filter
{
	public function filterEmptyRows(array $rows)
	{
		foreach ($rows as $key => $row) {
			if ($this->_isEmpty($row)) {
				unset($rows[$key]);
			}
		}

		return array_values($rows);
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