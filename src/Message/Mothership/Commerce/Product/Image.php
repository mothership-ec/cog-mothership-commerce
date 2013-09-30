<?php

namespace Message\Mothership\Commerce\Product;

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
}