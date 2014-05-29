<?php

namespace Message\Mothership\Commerce\Product\Barcode\Sheet;

abstract class AbstractSheet implements SheetInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getLabelsPerPage()
	{
		return $this->getXCount() * $this->getYCount();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBarcodeWidth()
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getViewReference()
	{
		return 'Message:Mothership:Commerce::product:barcode:default';
	}
}