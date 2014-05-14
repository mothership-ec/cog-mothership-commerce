<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class Tabs extends Controller
{
	public function index($productID)
	{
		$product	= $this->get('product.loader')->getByID($productID);

		$tabs = array(
			'Attributes' => 'ms.commerce.product.edit.attributes',
		);

		if (count($product->details) > 0) {
			$tabs['Details']	= 'ms.commerce.product.edit.details';
		}

		$tabs['Pricing'] 	 = 'ms.commerce.product.edit.pricing';
		$tabs['Units']  	 = 'ms.commerce.product.edit.units';
		$tabs['Stock']  	 = 'ms.commerce.product.edit.stock';
		$tabs['Images']  	 = 'ms.commerce.product.edit.images';

		$current = $this->get('http.request.master')->get('_route');

		return $this->render('Message:Mothership:Commerce::product:tabs', array(
			'tabs'    	  => $tabs,
			'current' 	  => $current,
			'productID'   => $productID,
		));
	}
}