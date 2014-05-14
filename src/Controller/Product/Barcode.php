<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class Barcode extends Controller
{
	public function printBarcodes($barcodes)
	{
		$labelsPerPage = $this->get('product.barcode.sheet')->getLabelsPerPage();

		return $this->render($this->get('product.barcode.sheet')->getViewReference(), [
			'barcodes'      => $barcodes,
			'sheetName'     => $this->get('product.barcode.sheet')->getName(),
			'labelsPerPage' => $labelsPerPage,
			'pageBreak'     => $labelsPerPage,
		]);
	}

	/**
	 * Controller for printing one barcode for every unit
	 */
	public function stockTake()
	{
		return $this->forward('Message:Mothership:Commerce::Controller:Product:Barcode#printBarcodes', [
			'barcodes' => $this->get('product.barcode.generate')->getOneOfEach(),
		]);
	}
}