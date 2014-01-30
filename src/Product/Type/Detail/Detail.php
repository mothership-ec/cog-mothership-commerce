<?php

namespace Message\Mothership\Commerce\Product\Type\Detail;

class Detail
{
	public $productID;
	public $name;
	public $value;

	public function __construct($productID, $name, $value)
	{
		$this->productID	= $productID;
		$this->name			= $name;
		$this->value		= $value;
	}

	public function __toString()
	{
		return (string) $this->value;
	}
}