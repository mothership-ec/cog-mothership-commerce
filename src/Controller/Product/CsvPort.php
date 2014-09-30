<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Mothership\Commerce\Product\Upload\Exception\UploadFrontEndException;

use Message\Cog\Controller\Controller;
use Message\Cog\Filesystem\FileType\CSVFile;

class CsvPort extends Controller
{
	const SPREADSHEET_NAME = 'products';
	const VALID_ROWS_SESSION = 'product.csv.valid_rows';

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

			return $this->_renderPreview($data);
		}

		return $this->redirectToReferer();
	}

	public function template()
	{
		return $this->get('product.upload.csv_download')->download(self::SPREADSHEET_NAME);
	}

	public function createProducts()
	{
		$form = $this->createForm($this->get('product.form.upload_confirm'));

		$form->handleRequest();

		if ($form->isValid()) {
			$data = $form->getData();

			foreach ($this->get('http.session')->get(self::VALID_ROWS_SESSION) as $productRows) {
				try {
					if (!is_array($productRows)) {
						throw new \LogicException('Product rows expected to be array, ' . gettype($productRows) . ' given');
					}

					$productRow = array_values($productRows);
					$productRow = array_shift($productRow);
					$product    = $this->get('product.upload.product_builder')->build($productRow);
					$this->get('product.upload.create_dispatcher')->create($product, $data, $productRow);

					foreach ($productRows as $row) {
						$unit = $this->get('product.upload.unit_builder')->setBaseProduct($product)->build($row);
						$this->get('product.upload.unit_create_dispatcher')->create($unit, $data, $row);
					}
				}
				catch (UploadFrontEndException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		$completeEvent = $this->get('product.upload.complete_dispatcher')->dispatch();

		if ($completeEvent->getRoute()) {
			return $this->redirectToRoute(
				$completeEvent->getRoute(),
				$completeEvent->getParams()
			);
		}

		return $this->redirectToReferer();
	}

	private function _renderPreview(array $data)
	{
		$data = $this->get('product.upload.csv_converter')->convert($data['file']);
		$this->get('product.upload.filter')->filter($data);

		$this->get('product.upload.validator')->validate($data);
		$validRows   = $this->get('product.upload.validator')->getValidRows();
		$invalidRows = $this->get('product.upload.validator')->getInvalidRows();

		$productData = $this->get('product.upload.unique_sorter')->sort($validRows);

		$this->get('http.session')->set(self::VALID_ROWS_SESSION, $productData);

		$form = $this->createForm($this->get('product.form.upload_confirm'));

		return $this->render('Message:Mothership:Commerce::product:csv:preview', [
			'heading'     => $this->get('product.upload.csv_heading'),
			'productData' => $productData,
			'invalid'     => $invalidRows,
			'form'        => $form,
		]);
	}
}