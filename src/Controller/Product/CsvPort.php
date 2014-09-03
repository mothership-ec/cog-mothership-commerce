<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class CsvPort extends Controller
{
	const SPREADSHEET_NAME = 'products';

	public function index()
	{
		$form = $this->createForm($this->get('product.form.csv_upload'));

		$form->handleRequest();

		$form->isValid();

		return $this->render('Message:Mothership:Commerce::product:csv:upload', [
			'form' => $form,
		]);
	}

	public function template()
	{
		return $this->get('product.upload.csv_download')->download(self::SPREADSHEET_NAME);
	}
}