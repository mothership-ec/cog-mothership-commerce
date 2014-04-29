<?php

namespace Message\Mothership\Commerce\Product\Barcode;

use Message\Cog\DB\Query;
use Image_Barcode2 as ImageBarcode;

class Generate
{
	const BARCODE_LOCATION = 'cog://public/barcodes';

	/**
	 * @var \Message\Cog\DB\Query
	 */
	protected $_query;

	protected $_height  = 60;
	protected $_width   = 1;
	protected $_fileExt = 'png';

	protected function __construct(Query $query)
	{
		$this->_query = $query;
	}

	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->_height;
	}

	public function setHeight($height)
	{
		if (!is_numeric($height)) {
			throw new \LogicException('$height must be a numeric value');
		}

		$this->_height = $height;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->_width;
	}

	public function setWidth($width)
	{
		if (!is_numeric($width)) {
			throw new \LogicException('$width must be a numeric value');
		}

		$this->_width = $width;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFileExt()
	{
		return $this->_fileExt;
	}

	public function setFileExt($ext)
	{
		if (!is_string($ext)) {
			throw new \LogicException('$ext must be a string, ' . gettype($ext) . ' given');
		}

		$this->_fileExt = $ext;

		return $this;
	}

	/**
	 * This loads only the information we need. In the hopefully near future we will swap this out for the normal
	 * product loader, but as it stands it does not load the products efficiently enough and will break if the load
	 * is too high, see https://github.com/messagedigital/cog-mothership-commerce/issues/297
	 *
	 * @param $unitIDs array                  Not currently used but will be useful when dealing with individual units
	 *
	 * @return \Message\Cog\DB\Result
	 */
	protected function _getUnitInfo($unitIDs = [])
	{
		return $this->_query->run("
			SELECT DISTINCT
				p.brand,
				p.name,
				u.barcode,
				up.price,
				up.currency_id AS currency,
				GROUP_CONCAT(o.option_value, ', ') AS options
			FROM
				product_unit AS u
			LEFT JOIN
				product AS p
			USING
				(product_id)
			LEFT JOIN
				product_unit_option AS o
			USING
				(unit_id)
			LEFT JOIN
				product_unit_price AS up
			USING
				(unit_id)
			WHERE
				barcode IS NOT NULL
			AND
				barcode != ''
			AND
				up.type = :retail?s
			GROUP BY
				u.unit_id
		", [
			'retail' => 'retail',
		])->bindTo('\\Message\\Mothership\\Commerce\\Product\\Barcode\\Barcode');
	}

	protected function _getBarcodeType()
	{
		return ImageBarcode::BARCODE_CODE39;
	}

	/**
	 * Method to return the url of the barcode image file. If no image exists, it creates the image and saves it to
	 * the file system
	 *
	 * @param $barcode
	 *
	 * @return string       Returns location of the barcode image file
	 */
	protected function _getBarcodeUrl($barcode)
	{
		if (!file_exists($this->_getFilePath($barcode))) {
			$image = ImageBarcode::draw(
				$barcode,
				$this->_getBarcodeType(),
				$this->getFileExt(),
				false,
				$this->getHeight(),
				$this->getWidth()
			);

			$filename = $this->_getFilename($barcode);

			$this->_saveImage($image, $filename);
		}

		return $this->_getFilePath($barcode);
	}

	/**
	 * Saves the barcode image to the barcodes directory
	 *
	 * @param $image
	 * @param $barcode
	 *
	 * @throws \LogicException     Throws exception if the file extension is not currently supported by the system
	 */
	protected function _saveImage($image, $barcode)
	{
		$ext = $this->_getFileExt();

		switch ($ext) {
			case 'png' :
				imagepng($image, $this->_getFilePath($barcode));
				break;
			default :
				throw new \LogicException($ext .' is not a supported file extension');
		}
	}

	/**
	 * Returns a filename generated using a hash of the barcode and attributes such as the size and file type
	 *
	 * @param $barcode
	 *
	 * @return string
	 */
	protected function _getFilename($barcode)
	{
		return md5(
			$barcode .
			$this->getFileExt() .
			$this->getHeight() .
			$this->getWidth()
		) . '.' . $this->getFileExt();
	}

	/**
	 * Returns appropriate filepath for a specific barcode
	 *
	 * @param string $barcode      Barcode for a product unit
	 *
	 * @return string              Returns the location of the barcode for that product unit
	 */
	protected function _getFilePath($barcode)
	{
		return $this->_getBarcodeLocation() . '/' . $this->_getFilename($barcode);
	}

	/**
	 * Return barcode directory, creating it if it doesn't exist
	 * Sets the umask to ensure that the directory created is public, then resets it back once the directory has been
	 * created
	 *
	 * @return string       Returns directory barcodes are saved to
	 */
	protected function _getBarcodeLocation()
	{
		if (!is_dir(self::BARCODE_LOCATION)) {
			$oldMask = umask(0);
			mkdir(self::BARCODE_LOCATION, 0777);
			umask($oldMask);
		}

		return self::BARCODE_LOCATION;
	}
}