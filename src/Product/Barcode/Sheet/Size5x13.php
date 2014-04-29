<?php

namespace Message\Mothership\Commerce\Product\Barcode\Sheet;

class Size5x13 extends AbstractSheet
{
	const X_COUNT = 5;
	const Y_COUNT = 13;

	public function getXCount()
	{
		return self::X_COUNT;
	}

	public function getYCount()
	{
		return self::Y_COUNT;
	}

	public function getName()
	{
		return '5x13';
	}

	public function getBarcodeHeight()
	{
		return 30;
	}
}