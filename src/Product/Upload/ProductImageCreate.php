<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product;

/**
 * Class ProductImageCreate
 * @package Message\Mothership\Commerce\Product\Upload
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class for using data from CSV to load and an image to the newly created product, and save it
 */
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

	/**
	 * @param Product\Image\Assignor $imageAssignor
	 * @param Product\Image\Create $imageCreate
	 * @param HeadingKeys $headingKeys
	 */
	public function __construct(Product\Image\Assignor $imageAssignor, Product\Image\Create $imageCreate, HeadingKeys $headingKeys)
	{
		$this->_imageAssignor = $imageAssignor;
		$this->_imageCreate   = $imageCreate;
		$this->_headingKeys   = $headingKeys;
	}

	/**
	 * Assign the image to the product using the filename given in the CSV, and save it against the product
	 *
	 * @param Product\Product $product
	 * @param array $row
	 * @param array $options
	 * @param string $type
	 */
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