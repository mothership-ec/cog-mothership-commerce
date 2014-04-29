<?php

namespace Message\Mothership\Commerce\Product\Barcode;

/**
 * Class to represent the barcode object to pass the view file
 *
 * Class Barcode
 * @package Message\Mothership\Commerce\Product\Barcode
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Barcode
{
	public $brand;
	public $name;
	public $barcode;
	public $price;
	public $currency;
	public $text;
	public $url;
}