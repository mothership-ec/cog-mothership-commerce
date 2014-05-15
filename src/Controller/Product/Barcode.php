<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Mothership\Commerce\Product\Product;
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

			if ($data['type'] === 'automatic') {
				if (!$data['location']) {
					throw new \LogicException('Location not set!');
				}
				$quantities = $this->_getQuantitiesFromStock($product, $data['location']);
			}
			else {
				$quantities = $this->_getQuantitiesFromData($data);
			}

			$offset   = (int) $data['offset'] ?: 0;
			$barcodes = $this->get('product.barcode.generate')->getBarcodes($quantities, $offset);

			return $this->forward('Message:Mothership:Commerce::Controller:Product:Barcode#printBarcodes', [
				'barcodes' => $barcodes,
			]);
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

	protected function _getQuantitiesFromData(array $data)
	{
		$quantities = [];

		foreach ($data as $key => $value) {
			if (false !== strpos($key, 'unit_')) {
				$unitID = explode('_', $key);
				$unitID = (int) array_pop($unitID);

				$quantities[$unitID] = (int) $value;
			}
		}

		return $quantities;
	}

	protected function _getQuantitiesFromStock(Product $product, $location)
	{
		if (!is_string($location)) {
			throw new \InvalidArgumentException('Location must be a string!');
		}

		$units      = $this->get('product.unit.loader')->includeOutOfStock(false)->getByProduct($product);
		$quantities = [];

		foreach ($units as $unit) {
			$stock = (array_key_exists($location, $unit->stock)) ? (int) $unit->stock[$location] : 0;
			$quantities[$unit->id] = $stock;
		}

		return $quantities;
	}
}