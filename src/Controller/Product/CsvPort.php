<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\Filesystem\FileType\CSVFile;

class CsvPort extends Controller
{
	const SPREADSHEET_NAME = 'products';

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
			$data = $this->get('product.upload.csv_filter')->filterEmptyRows(
				$this->get('product.upload.csv_converter')->convert($data['file'])
			);

			$this->get('product.upload.csv_validator')->validate($data);

			return $this->render('Message:Mothership:Commerce::product:csv:preview', [
				'heading' => $this->get('product.upload.csv_heading'),
				'valid' => $this->get('product.upload.csv_validator')->getValidRows(),
				'invalid' => $this->get('product.upload.csv_validator')->getInvalidRows(),
			]);
		}

		return $this->redirectToReferer();

	}

	public function template()
	{
		return $this->get('product.upload.csv_download')->download(self::SPREADSHEET_NAME);
	}
}