<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product;

class ProductImageCreate
{
	const HEADING_KEY = 'defaultImage';

	/**
	 * @var Product\Image\Assignor
	 */
	private $_imageAssignor;

	/**
	 * @var Product\Image\Create
	 */
	private $_imageCreate;

	/**
	 * @var HeadingKeys
	 */
	private $_headingKeys;

	public function __construct(Product\Image\Assignor $imageAssignor, Product\Image\Create $imageCreate, HeadingKeys $headingKeys)
	{
		$this->_imageAssignor = $imageAssignor;
		$this->_imageCreate   = $imageCreate;
		$this->_headingKeys   = $headingKeys;
	}

	public function save(Product\Product $product, array $row, $options = [], $type = 'default')
	{
		$key = $this->_headingKeys->getKey(self::HEADING_KEY);

		if (!array_key_exists($key, $row)) {
			throw new \LogicException('Key `' . $key . '` does not exist in row');
		}

		$filename = (string) $row[$key];

		if ($filename !== '') {
			$image = $this->_imageAssignor->assignByName($filename, $product, $options, $type);
			$this->_imageCreate->create($image);
		}
	}

}