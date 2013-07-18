<?php

namespace Message\Mothership\Commerce\Product\Unit;

class Unit
{
	public $id;
	public $weight;
	public $stock = array(
		1 => 0,
		2 => 0,
		3 => 0,
		4 => 0,
	);

	public $price = array(
		'GBP' => array(
			'retail' => 0,
			'rrp'    => 0,
			'cost'   => 0,
		),
	);

	public $sku;
	public $barcode;
	public $visible;
}