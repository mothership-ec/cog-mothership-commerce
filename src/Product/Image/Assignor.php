<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\FileManager\File;

/**
 * Class Assigner
 * @package Message\Mothership\Commerce\Product\Image
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class for assigning images to products using minimal details, e.g. filename
 */
class Assignor
{
	/**
	 * @var File\FileLoader
	 */
	private $_fileLoader;

	/**
	 * @var
	 */
	private $_locale;

	/**
	 * @param File\FileLoader $fileLoader
	 * @param $locale
	 */
	public function __construct(File\FileLoader $fileLoader, $locale)
	{
		$this->_fileLoader = $fileLoader;
		$this->_locale     = $locale;
	}

	/**
	 * Load image using by the filename, and assign a product to it.
	 *
	 * @param string $name                      The filename for the image
	 * @param Product $product                  The product to assign to the image
	 * @param array $options                    The product options to assign to the image
	 * @param string $type                      The type to set against the image
	 * @throws Exception\AssignmentException    Throws exception if file cannot be found
	 * @throws Exception\AssignmentException    Throws exception if file is not an image
	 *
	 * @return Image                            Returns a new Image instance with the product assigned
	 */
	public function assignByName($name, Product $product, array $options = [], $type = 'default')
	{
		$file = $this->_fileLoader->getByFilename($name);

		if (is_array($file)) {
			$file = array_shift($file);
		}

		if (false === $file) {
			throw new Exception\AssignmentException('Could not find image by name of `' . $name . '`');
		}

		if ($file->typeID !== File\Type::IMAGE) {
			throw new Exception\AssignmentException('File with name `' . $name . '` is not an image file');
		}

		return $this->_setProductToImage($product, $file, $options, $type);
	}

	/**
	 * Assign the product and the loaded file to an image instance
	 *
	 * @param Product $product             The product to assign to the image
	 * @param File\File $file              The loaded file to assign to the image
	 * @param array $options               The product options to assign to the image
	 * @param string $type                 The type to set against the image
	 * @throws \InvalidArgumentException   Throws exception if $type is not a string
	 *
	 * @return Image                       Returns a new Image instance with the product and file assigned
	 */
	private function _setProductToImage(Product $product, File\File $file, array $options, $type)
	{
		if (!is_string($type)) {
			throw new \InvalidArgumentException('Image type must be a string, ' . gettype($type) . ' given');
		}

		$image = new Image;
		$image->setFileLoader($this->_fileLoader);
		$image->product = $product;
		$image->fileID  = $file->id;
		$image->locale  = $this->_locale;
		$image->options = $options;
		$image->type    = $type;

		return $image;
	}
}