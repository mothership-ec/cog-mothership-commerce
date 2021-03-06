<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Mothership\FileManager\File\File;
use Message\Mothership\FileManager\File\Loader as FileLoader;

use Message\ImageResize\ResizableInterface;

use Message\Cog\ValueObject\Authorship;

use Exception;

class Image implements ResizableInterface
{
	public $id;
	public $authorship;
	public $type;
	public $locale;
	public $fileID;
	public $options = array();

	public $product;

	protected $_file;
	protected $_fileLoader;

	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship
			->disableUpdate(); // remove when making update class
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

	public function __get($key)
	{
		if ('file' == $key) {
			return $this->getFile();
		}
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
			'options',
			'product',
			'_file',
		);
	}
}