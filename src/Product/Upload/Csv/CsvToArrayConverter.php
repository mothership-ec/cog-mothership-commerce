<?php

namespace Message\Mothership\Commerce\Product\Upload\Csv;

use Message\Cog\Filesystem\FileType\CSVFile;

class CsvToArrayConverter
{
	public function convert(CSVFile $csv)
	{
		$rows = [];

		foreach ($csv as $row) {
			array_walk($row, function(&$value) {
				$value = trim($value);
			});

			$rows[] = $row;
		}

		return $rows;
	}
}