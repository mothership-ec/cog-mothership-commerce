<?php

namespace Message\Mothership\Commerce\Constraint\Product;

use Symfony\Component\Validator\Constraints\File as FileConstraint;

class Csv extends FileConstraint
{
	public $mimeTypes = [
		'text/csv',
		'text/plain',
	];

	public $mimeTypesMessage = 'The file type is invalid ({{ type }}). Please ensure that the file is a CSV.';
	public $csvColumnError   = 'Columns in uploaded CSV are not as expected';
	public $dataError        = 'Invalid data on file upload, please contact Message for assistance';
}