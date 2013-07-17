<?php

namespace Mothership\Commerce\Product\Entity\Unit;

class Unit
{
	public $id;
	public $weight;
	public $stock = array();
	public $price = array();
	public $sku;
	public $barcode;
	public $visible;
}