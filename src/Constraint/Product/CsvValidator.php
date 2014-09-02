<?php

namespace Message\Mothership\Commerce\Constraint\Product;

use Symfony\Component\Validator;

use Message\Cog\Service\Container;
use Message\Cog\Filesystem\FileType\CSVFile;
use Message\Cog\Filesystem\Exception\InvalidFileException;

class CsvValidator extends Validator\Constraints\FileValidator
{
	private $_heading;

	public function __construct()
	{
		$this->_heading = Container::get('product.upload.csv_heading')->getSimpleColumns();
	}

	public function validate($value, Validator\Constraint $constraint)
	{
		if (null === $value || '' === $value) {
			return;
		}

		parent::validate($value, $constraint);

		$file = new CSVFile($value->getPath());

		try {
			$columns = $file->getFirstLineAsColumns($this->_heading);
		}
		catch (InvalidFileException $e) {
			$this->context->addViolation('Columns in uploaded CSV are not as expected');
		}
	}
}