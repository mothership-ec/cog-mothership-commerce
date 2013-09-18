<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;

class Tabs extends Controller
{
	public function index($productID)
	{
		$tabs = array(
			'Attributes' => 'ms.commerce.product.edit.attributes',
			'Details'	 => 'ms.commerce.product.edit.details',
			'Pricing' 	 => 'ms.commerce.product.edit.pricing',
			'Units'  	 => 'ms.commerce.product.edit.units',
			'Stock'  	 => 'ms.commerce.product.edit.stock',
			'Images'  	 => 'ms.commerce.product.edit.images',
		);

		$current = $this->get('http.request.master')->get('_route');

		return $this->render('Message:Mothership:Commerce::product:tabs', array(
			'tabs'    	  => $tabs,
			'current' 	  => $current,
			'productID'   => $productID,
		));
	}
}