<?php

namespace Message\Mothership\Commerce\Product\Barcode\CodeGenerator;

use Image_Barcode2 as ImageBarcode;

class Code39Generator extends AbstractGenerator
{
	public function getName()
	{
		return ImageBarcode::BARCODE_CODE39;
	}

	public function getBarcodeType()
	{
		return ImageBarcode::BARCODE_CODE39;
	}
}