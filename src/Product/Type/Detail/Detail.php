<?php

namespace Message\Mothership\Commerce\Product\Type\Detail;

class Detail
{
	public $name;
	public $value;

	public function __toString()
	{
		return (string) $value;
	}
}