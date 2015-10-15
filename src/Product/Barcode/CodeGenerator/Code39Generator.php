<?php

namespace Message\Mothership\Commerce\Product\Barcode\CodeGenerator;

use Image_Barcode2 as ImageBarcode;

/**
 * Class Code39Generator
 * @package Message\Mothership\Commerce\Product\Barcode\CodeGenerator
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class for generating Code 39 type barcodes
 */
class Code39Generator extends AbstractGenerator
{
	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return ImageBarcode::BARCODE_CODE39;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBarcodeType()
	{
		return ImageBarcode::BARCODE_CODE39;
	}
}