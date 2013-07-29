<?php

namespace Message\Mothership\Commerce\Controller;

use Message\Cog\Controller\Controller;

class ProductsMenu extends Controller
{
	public function index($productID = null)
	{

		return $this->render('Message:Mothership:Commerce::products-menu', array(
			'productID' => $productID,
		));
	}
}