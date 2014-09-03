<?php

namespace Message\Mothership\Commerce\Constraint\Product;

use Symfony\Component\Validator;

use Message\Cog\Service\Container;
use Message\Cog\Filesystem\FileType\CSVFile;
use Message\Cog\Filesystem\Exception\InvalidFileException;

class CsvValidator extends Validator\Constraints\FileValidator
{
	private $_heading;

	/**
	 * @todo investigate whether or not it's possible to inject the heading columns rather than call
	 * it statically
	 */
	public function __construct()
	{
		$this->_heading = array_values(
			Container::get('product.upload.csv_heading')->getSimpleColumns()
		);
	}

	/**
	 * Validate that the upload is a CSV (or plain text) file, and that the columns match those expected
	 *
	 * @param mixed $value
	 * @param Validator\Constraint $constraint
	 * @throws \InvalidArgumentException
	 */
	public function validate($value, Validator\Constraint $constraint)
	{
		if (!$constraint instanceof Csv) {
			throw new \InvalidArgumentException('Constraint must be an instance of Csv');
		}

		if (null === $value || '' === $value) {
			return;
		}

		if (!$value instanceof CSVFile) {
			throw new \LogicException('Value must be an instance of CSVFile');
		}

		parent::validate($value, $constraint);

		try {
			$value->getFirstLineAsColumns($this->_heading);
		}
		catch (InvalidFileException $e) {
			$this->context->addViolation($constraint->csvColumnError);
		}
		catch (\Exception $e) {
			$this->context->addViolation($constraint->uploadErrorMessage);
		}
	}
}