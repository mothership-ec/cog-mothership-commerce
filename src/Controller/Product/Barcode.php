<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Image_Barcode2 as ImageBarcode;

class Barcode extends Controller
{
	public function printBarcodes()
	{

	}

	/**
	 * Controller for printing one barcode for every unit
	 */
	public function stockTake()
	{
		$units =

		de(ImageBarcode::draw('HELLO', ImageBarcode::BARCODE_CODE39));
	}
}