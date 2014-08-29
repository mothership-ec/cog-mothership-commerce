<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class CsvPort extends Controller
{
	public function index()
	{

		return $this->render('Message:Mothership:Commerce::product:csv:upload');
	}

	public function template()
	{
		return $this->get('product.upload.csv_download')->download();
	}
}