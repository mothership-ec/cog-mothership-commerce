<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class Barcode extends Controller
{
	public function productBarcodes($productID)
	{
		$product = $this->get('product.loader')->getByID($productID);
		$units   = $this->get('product.unit.loader')->includeOutOfStock(true)->getByProduct($product);
		$form    = $this->createForm($this->get('product.form.barcode')->setUnits($units));

		$form->handleRequest();

		if ($form->isValid()) {
			$data = $form->getData();
			de($data);
		}

		return $this->render('Message:Mothership:Commerce::product:barcode:form', [
			'form' => $form,
			'units' => $units,
		]);
	}

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