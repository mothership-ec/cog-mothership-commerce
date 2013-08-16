<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.product']->setParent('ms.cp')->setPrefix('/product');
		$router['ms.order']->setParent('ms.cp')->setPrefix('/order');

		$router['ms.product']->add('ms.commerce.product.dashboard', '', '::Controller:Product:Dashboard#index');

		$router['ms.product']->add('ms.commerce.product.create.action', 'create', '::Controller:Product:Create#process')
			->setMethod('POST');

		$router['ms.product']->add('ms.commerce.product.create', 'create', '::Controller:Product:Create#index');

		$router['ms.product']->add('ms.commerce.product.edit.action', 'edit/{productID}', '::Controller:Product:Edit#process')
			->setRequirement('productID', '\d+')
			->setMethod('POST');

		$router['ms.product']->add('ms.commerce.product.edit', 'edit/{productID}', '::Controller:Product:Edit#index')
			->setRequirement('productID', '\d+');

		$router['ms.product']->add('ms.commerce.product.edit.units.action', 'product/edit/{productID}/units', '::Controller:Product:Edit#unitProcess')
			->setRequirement('productID', '\d+')
			->setMethod('POST');

		$router['ms.product']->add('ms.commerce.product.edit.units.create.action', 'product/edit/{productID}/units/create', '::Controller:Product:Edit#addUnitProccess')
			->setRequirement('productID', '\d+')
			->setMethod('POST');

		$router['ms.product']->add('ms.commerce.product.edit.units', 'edit/{productID}/units', '::Controller:Product:Edit#units')
			->setRequirement('productID', '\d+');
		$router['ms.product']->add('ms.commerce.product.edit.stock.action', 'edit/{productID}/stock', '::Controller:Product:Edit#processStock')
			->setRequirement('productID', '\d+')
			->setMethod('POST');
		$router['ms.product']->add('ms.commerce.product.edit.stock', 'edit/{productID}/stock', '::Controller:Product:Edit#stock')
			->setRequirement('productID', '\d+');
		$router['ms.product']->add('ms.commerce.product.edit.images.action', 'edit/{productID}/images', '::Controller:Product:Edit#imagesProcess')
			->setRequirement('productID', '\d+')
			->setMethod('POST');
		$router['ms.product']->add('ms.commerce.product.edit.images', 'edit/{productID}/images', '::Controller:Product:Edit#images')
			->setRequirement('productID', '\d+');


		$router['ms.product.basket']->add('ms.commerce.product.add.basket', '/basket/add/{productID}', '::Controller:Module:ProductSelector#process')
			->setMethod('POST');


		$router['ms.order']->add('ms.commerce.order.view.dashboard', 'view', '::Controller:Order:Listing#dashboard');
		$router['ms.order']->add('ms.commerce.order.view.all', 'view/all', '::Controller:Order:Listing#all');
		$router['ms.order']->add('ms.commerce.order.view.shipped', 'view/shipped', '::Controller:Order:Listing#shipped');
		$router['ms.order']->add('ms.commerce.order.search.action', 'search', '::Controller:Order:Listing#searchAction')
			->setMethod('POST');

		$router['ms.order']->add('ms.commerce.order.detail.view.index', 'view/{orderID}', '::Controller:Order:OrderDetail#orderOverview')
			->setRequirement('orderID', '\d+');
		$router['ms.order']->add('ms.commerce.order.detail.view.items', 'view/{orderID}/item', '::Controller:Order:OrderDetail#itemListing')
			->setRequirement('orderID', '\d+');
		$router['ms.order']->add('ms.commerce.order.detail.view.addresses', 'view/{orderID}/address', '::Controller:Order:OrderDetail#addressListing')
			->setRequirement('orderID', '\d+');
		$router['ms.order']->add('ms.commerce.order.detail.view.payments', 'view/{orderID}/payment', '::Controller:Order:OrderDetail#paymentListing')
			->setRequirement('orderID', '\d+');
		$router['ms.order']->add('ms.commerce.order.detail.view.dispatches', 'view/{orderID}/dispatch', '::Controller:Order:OrderDetail#dispatchListing')
			->setRequirement('orderID', '\d+');
		$router['ms.order']->add('ms.commerce.order.detail.view.notes', 'view/{orderID}/note', '::Controller:Order:OrderDetail#noteListing')
			->setRequirement('orderID', '\d+');
	}
}