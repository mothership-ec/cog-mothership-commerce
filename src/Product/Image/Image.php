<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Mothership\FileManager\File\File;

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
			->disableUpdate()
			->disableDelete();
	}

	public function getUrl()
	{
		return $this->getFile()->getUrl();
	}

	public function getAltText()
	{
		return $this->getFile()->getAltText();
	}

	public function setFileLoader($fileLoader)
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
		if (!$this->_file) {
			$this->_loadFile();
		}

		return $this->_file;
	}

	protected function _loadFile()
	{
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
		$this->getFile();

		return array(
			'id',
			'authorship',
			'type',
			'locale',
			'options',
			'product',
			'_file',
		);
	}
}