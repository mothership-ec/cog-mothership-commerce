<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class CsvPort extends Controller
{
	const SPREADSHEET_NAME = 'products';

	public function index()
	{
		de($this->get('product.upload.csv_heading')->getSimpleColumns());
		return $this->render('Message:Mothership:Commerce::product:csv:upload');
	}

	public function template()
	{
		return $this->get('product.upload.csv_download')->download(self::SPREADSHEET_NAME);
	}
}