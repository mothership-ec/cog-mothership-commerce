<?php

namespace Message\Mothership\Commerce\Product\Barcode\Sheet;

class Size3x8 extends AbstractSheet
{
	const X_COUNT = 3;
	const Y_COUNT = 8;

	/**
	 * {@inheritdoc}
	 */
	public function getXCount()
	{
		return self::X_COUNT;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getYCount()
	{
		return self::Y_COUNT;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return '3x8';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBarcodeHeight()
	{
		return 30;
	}
}