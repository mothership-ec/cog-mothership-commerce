<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class ProductsMenu extends Controller
{
	public function index($productID = null)
	{

		return $this->render('Message:Mothership:Commerce::product:products-menu', array(
			'productID' => $productID,
		));
	}
}