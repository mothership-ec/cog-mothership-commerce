<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Mothership\FileManager\File\File;
use Message\Mothership\FileManager\File\Loader as FileLoader;

use Message\ImageResize\ResizableInterface;

use Message\Cog\ValueObject\Authorship;

use Exception;

/**
 * Class Image
 * @package Message\Mothership\Commerce\Product\Image
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class Image implements ResizableInterface
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var Authorship
	 */
	public $authorship;

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var
	 */
	public $locale;
	public $fileID;
	public $product;

	protected $_file;

	protected $_fileLoader;

	private $_options = [];

	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship
			->disableUpdate(); // remove when making update class
	}

	public function &__get($key)
	{
		if ('file' == $key) {
			if (null === $this->_file) {
				$this->_loadFile();
			}

			return $this->_file;
		}

		if ('options' == $key) {
			$this->_options = $this->_cleanOptions($this->_options);

			return $this->_options;
		}
	}

	public function __set($key, $value)
	{
		if ('options' === $key) {
			$this->setOptions($value);
		}
	}

	public function __isset($key)
	{
		return ('file' === $key);
	}

	public function __sleep()
	{
		$this->_loadFile();

		return array(
			'id',
			'authorship',
			'type',
			'locale',
			'fileID',
			'product',
			'_options',
			'_file'
		);
	}

	public function getUrl()
	{
		if (!$this->getFile()) {
			return null;
		}

		return $this->getFile()->getUrl();
	}

	public function getAltText()
	{
		if (!$this->getFile()) {
			return null;
		}

		return $this->getFile()->getAltText();
	}

	public function setFileLoader(FileLoader $fileLoader)
	{
		$this->_fileLoader = $fileLoader;
	}

	public function setOptions(array $options)
	{
		$this->_options = $this->_cleanOptions($options);
	}

	public function getOptions()
	{
		return $this->_options;
	}

	public function getFile()
	{
		if (null === $this->_file) {
			$this->_loadFile();
		}

		return $this->_file;
	}

	protected function _loadFile()
	{
		if (null !== $this->_file) {
			return;
		}

		if (!$this->_fileLoader) {
			throw new \LogicException(__CLASS__ . ': No file loader set, has this object been serialized?');
		}

		$this->_file = $this->_fileLoader->getByID($this->fileID);
	}

	private function _cleanOptions($options)
	{
		$clean = [];

		foreach ($options as $name => $value) {
			$name = strtolower($name);
			if (array_key_exists($name, $clean)) {
				throw new \LogicException('Option names are parsed to lower case, `' . $name . '` already exists in image options');
			}

			$clean[$name] = $value;
		}

		return $clean;
	}
}