<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class Sidebar extends Controller
{
	public function index($productID = null)
	{
		$listing = array_flip($this->get('product.category.loader')->getAll());

		foreach ($listing as $category => $list) {
			$listing[$category] = $this->get('product.loader')->getByCategory($category, 20);
		}

		return $this->render('Message:Mothership:Commerce::product:sidebar', array(
			'listing'   => $listing,
			'productID' => $productID,
		));
	}
}