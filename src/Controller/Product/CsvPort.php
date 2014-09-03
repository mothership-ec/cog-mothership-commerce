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
			de($this->_convertDataToArray($data['file']));
		}

	}

	public function template()
	{
		return $this->get('product.upload.csv_download')->download(self::SPREADSHEET_NAME);
	}

	private function _convertDataToArray(CSVFile $csv)
	{
		$rows = [];

		foreach ($csv as $row) {
			$rows[] = $row;
		}

		return $rows;
	}
}