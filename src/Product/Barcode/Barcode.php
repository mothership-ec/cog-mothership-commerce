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
	/**
	 * @var int
	 */
	public $unitID;

	/**
	 * @var string
	 */
	public $brand;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $barcode;

	/**
	 * @var float
	 */
	public $price;

	/**
	 * @var string
	 */
	public $currency;

	/**
	 * @var string
	 */
	public $text;

	/**
	 * @var string
	 */
	public $url;

	/**
	 * @var \Message\Cog\Filesystem\File
	 */
	public $file;

	public function getBarcode()
	{
		return $this->barcode;
	}
}