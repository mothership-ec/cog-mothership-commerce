<?php

namespace Message\Mothership\Commerce\Product\Barcode\Sheet;

interface SheetInterface
{
	public function getLabelsPerPage();

	public function getXCount();

	public function getYCount();

	public function getViewReference();

	public function getHeight();

	public function getWidth();
}