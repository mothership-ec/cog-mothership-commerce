<?php

namespace Message\Mothership\Commerce\Product\Barcode\Sheet;

interface SheetInterface
{
	/**
	 * Get the total number of labels to appear on one page
	 *
	 * @return int
	 */
	public function getLabelsPerPage();

	/**
	 * Get the name of the sheet type, used to pull sheet from Collection
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get the view file that should be used to render barcodes
	 *
	 * @return string
	 */
	public function getViewReference();

	/**
	 * Get the number of barcodes to display in a row
	 *
	 * @return int
	 */
	public function getXCount();

	/**
	 * Get the number of barcodes to display in a column
	 *
	 * @return int
	 */
	public function getYCount();

	/**
	 * Height of barcode in pixels
	 *
	 * @return int
	 */
	public function getBarcodeHeight();

	/**
	 * Width of barcode, not in pixels. This is in the arbitrary unit that the Image_Barcode2 package uses to
	 * determine the width of barcode images. 1 is the default and is a standard width, 2 is double the width etc.
	 *
	 * @return int
	 */
	public function getBarcodeWidth();
}