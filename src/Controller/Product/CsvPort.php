<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class CsvPort extends Controller
{
	public function index()
	{
		de($this->get('product.upload.csv_columns')->getColumns());

		return $this->render('Message:Mothership:Commerce::product:csv:upload');
	}
}