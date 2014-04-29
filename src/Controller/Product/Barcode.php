<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class Barcode extends Controller
{
	public function printBarcodes($barcodes)
	{
		return $this->render('Message:Mothership:Commerce::product:barcodes', [
			'barcodes'   => $barcodes,
			'perPage'    => $this->_getPerPage(),
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

	/**
	 * @return int
	 */
	protected function _getPerPage()
	{
		return 24;
	}

}