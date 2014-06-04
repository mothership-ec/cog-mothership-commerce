<?php

namespace Message\Mothership\Commerce\Product\Barcode;

use Image_Barcode2 as ImageBarcode;
use Message\Cog\Filesystem\File;
use Message\Cog\Filesystem\StreamWrapperManager;

/**
 * Class for communicating with the barcode image resource
 *
 * Class Image
 * @package Message\Mothership\Commerce\Product\Barcode
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class ImageResource
{
	const BARCODE_LOCATION = 'barcodes';
	const PUBLIC_PATH      = 'cog://public';

	/**
	 * @param $barcode string
	 * @param $type string
	 * @param $fileExt string
	 * @param $height int
	 * @param $width int
	 *
	 * @return resource
	 */
	public function getResource($barcode, $type, $fileExt, $height, $width)
	{
		return ImageBarcode::draw(
			$barcode,
			$type,
			$fileExt,
			false,
			$height,
			$width
		);
	}

	/**
	 * Get a File instance for the image
	 *
	 * @param $filename
	 * @return File
	 */
	public function getFile($filename)
	{
		return new File($this->getPath($filename));
	}

	/**
	 * Saves the barcode image to the barcodes directory
	 *
	 * @param $image
	 * @param $filename
	 * @param $ext
	 * @throws \InvalidArgumentException
	 *
	 * @return bool
	 */
	public function save($image, $filename, $ext)
	{
		$path = $this->getPath($filename);

		if (!is_resource($image)) {
			throw new \InvalidArgumentException('$image must be a file resource');
		}

		switch ($ext) {
			case 'png' :
				imagepng($image, $path);
				break;
			case 'jpg':
			case 'jpeg':
				imagejpeg($image, $path);
				break;
			case 'gif':
				imagegif($image, $path);
				break;
			default:
				return false;
		}

		return true;
	}

	/**
	 * @param $filename
	 *
	 * @return bool
	 */
	public function exists($filename)
	{
		return file_exists($this->getPath($filename));
	}

	/**
	 * Return barcode directory, creating it if it doesn't exist
	 * Sets the umask to ensure that the directory created is public, then resets it back once the directory has been
	 * created
	 *
	 * @return string       Returns directory barcodes are saved to
	 */
	public function getDirectory()
	{
		$location = self::PUBLIC_PATH . '/' . self::BARCODE_LOCATION;

		if (!is_dir($location)) {
			$oldMask = umask(0);
			mkdir($location, 0777);
			umask($oldMask);
		}

		return $location;
	}

	/**
	 * Get path of barcode image
	 *
	 * @param $filename
	 *
	 * @return string
	 */
	public function getPath($filename)
	{
		$path    = $this->getDirectory() . '/' . $filename;
		$handler = StreamWrapperManager::getHandler(File::COG_PREFIX);

		return $handler->getLocalPath($path, File::COG_PREFIX);
	}
}