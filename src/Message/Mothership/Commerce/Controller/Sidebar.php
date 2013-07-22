<?php

namespace Message\Mothership\Commerce\Controller;

use Message\Cog\Controller\Controller;

class Sidebar extends Controller
{
	public function index($productID = null)
	{
		$loader = $this->get('product.loader');
		$products  = $loader->getAll();
		return $this->render('Message:Mothership:Commerce::sidebar', array(
			'products'      => $products,
			'productID' => $productID,
		));
	}
}