<?php

namespace Message\Mothership\Commerce\Controller;

use Message\Cog\Controller\Controller;

class ProductsMenu extends Controller
{
	public function index($productID = null)
	{
		$loader = $this->get('product.loader');
		$products  = $loader->getAll();

		return $this->render('Message:Mothership:Commerce::productsMenu', array(
			'productID' => $productID,
		));
	}
}