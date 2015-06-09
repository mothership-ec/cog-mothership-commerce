<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\FileManager\File;

/**
 * Class Assigner
 * @package Message\Mothership\Commerce\Product\Image
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class Assignor
{
	private $_fileLoader;
	private $_locale;

	public function __construct(File\FileLoader $fileLoader, $locale)
	{
		$this->_fileLoader = $fileLoader;
		$this->_locale     = $locale;
	}

	public function assignByName($name, Product $product, $type = 'default', $options = [])
	{
		if (!is_string($type)) {
			throw new \InvalidArgumentException('Image type must be a string, ' . gettype($type) . ' given');
		}

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