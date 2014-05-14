<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class Sidebar extends Controller
{
	public function index($productID = null)
	{
		// @todo uncomment this stuff. Do NOT let it pass QA like this yo
//		$products = $this->get('product.loader')->getAll();
//		$listing  = array();
//
//		foreach ($products as $product) {
//			$listing[$product->category][] = $product;
//		}
		$listing = [];
		return $this->render('Message:Mothership:Commerce::product:sidebar', array(
			'listing'   => $listing,
			'productID' => $productID,
		));
	}
}