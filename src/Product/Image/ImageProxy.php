<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Mothership\FileManager\File\File;
use Message\Mothership\FileManager\File\Loader as FileLoader;

use Message\ImageResize\ResizableInterface;

use Message\Cog\DB\Entity\EntityLoaderCollection;

class ImageProxy extends Image
{
	protected $_fileLoader;

	public function __construct(FileLoader $fileLoader)
	{
		parent::construct();

		$this->_fileLoader = $fileLoader;
	}

	public function getFile()
	{
		$this->_loadFile();

		return parent::getFile();
	}

	protected function _loadFile()
	{
		if (null !== $this->_file) {
			return;
		}

		$this->_file = $this->_fileLoader->getByID($this->fileID);
	}
}