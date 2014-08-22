<?php

namespace Message\Mothership\Commerce\Product\Upload\Csv;

use Message\Cog\ValueObject\Collection;

class Table
{
	protected $_rows;

	public function __construct(array $rows)
	{
		foreach ($rows as $row) {
			if (!$row instanceof Row) {
				throw new \InvalidArgumentException('Expecting be an instance of Row, ' . gettype($row) . ' given');
			}
		}

		$this->_rows = $rows;
	}
}