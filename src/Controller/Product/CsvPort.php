<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Mothership\Commerce\Product\Upload\Exception\UploadFrontEndException;
use Message\Mothership\Commerce\Product\Upload\SessionNames;

use Message\Cog\Controller\Controller;
use Message\Cog\Filesystem\FileType\CSVFile;

class CsvPort extends Controller
{
	const SPREADSHEET_NAME   = 'products';
	const VALID_ROWS_SESSION = 'product.csv.valid_rows';

	const NUM_DISPLAY_PRODUCTS = 6;
	const MAX_CELL_LENGTH = 50; // number of characters to display per cell in product table

	public function index()
	{
		$form = $this->createForm($this->get('product.form.csv_upload'));

		return $this->render('Message:Mothership:Commerce::product:csv:upload', [
			'form' => $form,
		]);
	}

	public function preview()
	{
		$form = $this->createForm($this->get('product.form.csv_upload'));

		$form->handleRequest();

		if ($form->isValid()) {
			$data = $form->getData();
			$data = $this->get('product.upload.csv_converter')->convert($data['file']);
			$this->get('product.upload.filter')->filter($data);

			$this->get('product.upload.validator')->validate($data);
			$validRows = $this->get('product.upload.validator')->getValidRows();

			$invalidRows = $this->get('product.upload.validator')->getInvalidRows();

			if (empty($validRows) && empty($invalidRows)) {
				$this->addFlash('error', $this->trans('ms.commerce.product.upload.no-valid-rows'));
				return $this->redirectToReferer();
			}

			$productData = $this->get('product.upload.unique_sorter')->sort($validRows);

			$this->get('http.session')->set(SessionNames::VALID_ROWS_SESSION, $productData);

			$form = $this->createForm($this->get('product.form.upload_confirm'));

			return $this->render('Message:Mothership:Commerce::product:csv:preview', [
				'heading'       => $this->get('product.upload.csv_heading'),
				'productData'   => $productData,
				'invalid'       => $invalidRows,
				'maxCellLength' => self::MAX_CELL_LENGTH,
				'form'          => $form,
			]);
		}

		return $this->redirectToReferer();
	}

	public function template()
	{
		return $this->get('product.upload.csv_download')->download(SessionNames::SPREADSHEET_NAME);
	}

	public function createProducts()
	{
		$form = $this->createForm($this->get('product.form.upload_confirm'));

		$form->handleRequest();

		$productCount = 0;
		$unitCount    = 0;

		if ($form->isValid()) {
			$data = $form->getData();

			$groupedProductRows = $this->get('http.session')->get(SessionNames::VALID_ROWS_SESSION);

			foreach ($groupedProductRows as $productRows) {
				try {
					if (!is_array($productRows)) {
						throw new \LogicException('Product rows expected to be array, ' . gettype($productRows) . ' given');
					}

					$productRow = array_values($productRows);
					$productRow = array_shift($productRow);
					$product    = $this->get('product.upload.product_builder')->build($productRow);

					$this->get('product.upload.create_dispatcher')->create($product, $data, $productRow);
					$productCount++;

					foreach ($productRows as $row) {
						$unit = $this->get('product.upload.unit_builder')->setBaseProduct($product)->build($row);
						$unit = $this->get('product.upload.unit_create_dispatcher')->create($unit, $data, $row);
						$this->get('product.upload.unit_stock')->setStockLevel($unit, $row);
						$unitCount++;
					}
				}
				catch (UploadFrontEndException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}

			$completeEvent = $this->get('product.upload.complete_dispatcher')->dispatch();

			$this->_addSuccessFlash($productCount, $unitCount);

			if ($completeEvent->getRoute()) {
				return $this->redirectToRoute(
					$completeEvent->getRoute(),
					$completeEvent->getParams()
				);
			}
		}

		return $this->redirectToReferer();
	}

	private function _addSuccessFlash($productCount, $unitCount)
	{
		$productCount = (int) $productCount;
		$unitCount    = (int) $unitCount;

		if ($productCount === 1 && $unitCount !== 1) {
			$transChoice = 1;
		} elseif ($productCount !== 1 && $unitCount === 1) {
			$transChoice = 2;
		} elseif ($productCount === 1 && $unitCount === 1) {
			$transChoice = 3;
		} else {
			$transChoice = 0;
		}

		$this->addFlash('success', $this->get('translator')->transChoice(
			'ms.commerce.product.upload.success',
			$transChoice,
			[
				'%productCount%' => $productCount,
				'%unitCount%'    => $unitCount,
			]
		));
	}

	private function _renderPreview(array $data)
	{

	}
}