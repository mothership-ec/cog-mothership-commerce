<?php

namespace Message\Mothership\Commerce\Product\Type\Detail;

class Detail
{
	public $productID;
	public $name;
	public $value;
	public $valueInt;
	public $locale;

	public function __construct($productID = null, $name = '', $value = '', $locale = 'EN')
	{
		$this->productID	= $productID;
		$this->name			= $name;
		$this->value		= $value;
		$this->valueInt		= $value;
		$this->locale		= $locale;
	}

	public function __toString()
	{
		return (string) $this->value;
	}
}