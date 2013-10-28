<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Cog\ImageResize\ResizableInterface;
use Message\Cog\ValueObject\Authorship;

class Image implements ResizableInterface
{
	public $id;
	public $authorship;
	public $type;
	public $locale;
	public $file;
	public $options = array();

	public $product;

	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship
			->disableUpdate()
			->disableDelete();
	}

	public function getUrl()
	{
		return $this->file->getUrl();
	}

	public function getAltText()
	{
		return $this->file->getAltText();
	}

	public function __sleep()
	{
		return array(
			'id',
			'authorship',
			'type',
			'locale',
			'options',
			'product',
		);
	}

	public function __wakeup()
	{
		$this->file = \Message\Cog\Service\Container::get('file_manager.file.loader')->getByID($this->id);
	}
}