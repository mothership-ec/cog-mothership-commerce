<?php

namespace Message\Mothership\Commerce\Product\Barcode\Sheet;

abstract class AbstractSheet implements SheetInterface
{
	public function getLabelsPerPage()
	{
		return $this->getXCount() * $this->getYCount();
	}

	public function getBarcodeWidth()
	{
		return 1;
	}

	public function getViewReference()
	{
		return 'Message:Mothership:Commerce::product:barcodes';
	}
}