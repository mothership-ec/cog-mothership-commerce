<?php

namespace Message\Mothership\Commerce\Product\Barcode;

use Image_Barcode2 as ImageBarcode;

/**
 * Class ValidTypes
 * @package Message\Mothership\Commerce\Product\Barcode
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class holding some static values and methods for checking barcode type is supported.
 */
class ValidTypes
{
	/**
	 * @var array
	 */
	private static $_valid = [
		ImageBarcode::BARCODE_CODE39,
		ImageBarcode::BARCODE_INT25,
		ImageBarcode::BARCODE_EAN13,
		ImageBarcode::BARCODE_UPCA,
		ImageBarcode::BARCODE_UPCE,
		ImageBarcode::BARCODE_CODE128,
		ImageBarcode::BARCODE_EAN8,
		ImageBarcode::BARCODE_POSTNET,
	];

	/**
	 * Get an array of all valid barcode types
	 *
	 * @return array
	 */
	public static function getValidTypes()
	{
		return self::$_valid;
	}

	/**
	 * Check that a given barcode type is valid
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public static function isValid($type)
	{
		return in_array($type, self::getValidTypes(), true);
	}
}