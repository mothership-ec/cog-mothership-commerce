<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\ValueObject\Authorship;

class Image
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
}
