<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Image_Barcode2 as ImageBarcode;

class Barcode extends Controller
{
	const BARCODE_LOCATION = 'cog://public/barcodes';

	public function printBarcodes()
	{

	}

	/**
	 * Controller for printing one barcode for every unit
	 */
	public function stockTake()
	{
		$barcodes = [];
		$units = [];
		foreach ($this->_getProductBarcodes() as $barcode) {
			$barcodes[] = $this->_getBarcodeUrl($barcode);
		}
		de($barcodes);
	}

	protected function _getProductBarcodes()
	{
		return $this->get('db.query')->run("
			SELECT DISTINCT
				barcode
			FROM
				product_unit
			WHERE
				barcode IS NOT NULL
			AND
				barcode != ''
		")->flatten();
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
				$this->_getFileExt(),
				false,
				$this->_getHeight(),
				$this->_getWidth()
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
		return md5($barcode . $this->_getFileExt() . $this->_getHeight() . $this->_getWidth()) . '.' . $this->_getFileExt();
	}

	/**
	 * @return int
	 */
	protected function _getHeight()
	{
		return 60;
	}

	/**
	 * @return int
	 */
	protected function _getWidth()
	{
		return 1;
	}

	/**
	 * @return string
	 */
	protected function _getFileExt()
	{
		return 'png';
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