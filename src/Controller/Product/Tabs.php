<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Mothership\ControlPanel\Event\BuildMenuEvent;
use Message\Mothership\Commerce\Events;

class Tabs extends Controller
{
	public function index($productID, $saveButton = null)
	{
		$product	= $this->get('product.loader')->getByID($productID);

		$tabs = array(
			'Attributes' => 'ms.commerce.product.edit.attributes',
		);

		if ($product->getDetails()->count() > 0) {
			$tabs['Details']	= 'ms.commerce.product.edit.details';
		}

		$tabs['Pricing'] 	 = 'ms.commerce.product.edit.pricing';
		$tabs['Units']  	 = 'ms.commerce.product.edit.units';
		$tabs['Stock']  	 = 'ms.commerce.product.edit.stock';
		$tabs['Images']  	 = 'ms.commerce.product.edit.images';

		$event = new BuildMenuEvent();
		$current = $this->get('http.request.master')->get('_route');

		foreach ($tabs as $label => $route) {
			$event->addItem($route, $label, [], $current == $route ? ['active'] : []);
		}

		$this->get('event.dispatcher')->dispatch(Events::PRODUCT_ADMIN_TAB_BUILD, $event);

		$tabs = $event->getItems();

		return $this->render('Message:Mothership:Commerce::product:tabs', array(
			'tabs'    	  => $tabs,
			'productID'   => $productID,
			'saveButton'  => $saveButton
		));
	}
}