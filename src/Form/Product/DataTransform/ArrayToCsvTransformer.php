<?php

namespace Message\Mothership\Commerce\Form\Product\DataTransform;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Message\Cog\Filesystem\FileType\CSVFile;

class ArrayToCsvTransformer implements DataTransformerInterface
{
	public function transform($csv)
	{
		return $csv;
	}

	public function reverseTransform($csv)
	{
		if (empty($csv)) {
			return null;
		}

		if (!array_key_exists('tmp_name', $csv)) {
			throw new TransformationFailedException('`tmp_value` not in array');
		}

		return new CSVFile($csv['tmp_name']);
	}
}