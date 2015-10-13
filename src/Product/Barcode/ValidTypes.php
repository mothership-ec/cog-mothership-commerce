<?php

namespace Message\Mothership\Commerce\Product\Barcode;

use Image_Barcode2 as ImageBarcode;

class ValidTypes
{
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

	public static function getValidTypes()
	{
		return self::$_valid;
	}

	public static function isValid($type)
	{
		return in_array($type, self::getValidTypes(), true);
	}
}