<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class Sidebar extends Controller
{
	public function index($productID = null)
	{
		$loader = $this->get('product.loader');
		$products  = $loader->getAll();
		$listing = array();
		foreach ($products as $product) {
			$listing[$product->category][] = $product;
		}

		return $this->render('Message:Mothership:Commerce::product:sidebar', array(
			'listing'  => $listing,
			'productID' => $productID,
		));
	}
}