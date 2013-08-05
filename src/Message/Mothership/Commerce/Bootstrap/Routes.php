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
		$router['ms.product']->add('ms.commerce.product.edit.stock.action', 'edit/{productID}/stock', '::Controller:Product:Edit#stock')
			->setRequirement('productID', '\d+');
		$router['ms.product']->add('ms.commerce.product.edit.stock', 'edit/{productID}/stock', '::Controller:Product:Edit#stock')
			->setRequirement('productID', '\d+');
		$router['ms.product']->add('ms.commerce.product.edit.images.action', 'edit/{productID}/images', '::Controller:Product:Edit#imagesProcess')
			->setRequirement('productID', '\d+')
			->setMethod('POST');
		$router['ms.product']->add('ms.commerce.product.edit.images', 'edit/{productID}/images', '::Controller:Product:Edit#images')
			->setRequirement('productID', '\d+');

		
		$router['ms.product']->add('ms.commerce.product.add.basket', '/basket/add/{productID}', '::Controller:Module:ProductSelector#process')
			->setMethod('POST');


		$router['ms.order']->add('ms.commerce.order.view.all-orders', 'view', '::Controller:Order:Order#allOrders');


		$router['ms.order']->add('ms.commerce.order.detail.view.index', 'view/{orderID}', '::Controller:Order:Order#index')
			->setRequirement('orderID', '\d+');

		$router['ms.order']->add('ms.commerce.order.detail.view.order-overview', 'view/{orderID}/order-overview', '::Controller:Order:Order#orderOverview')
			->setRequirement('orderID', '\d+');

		$router['ms.order']->add('ms.commerce.order.detail.view.items', 'view/{orderID}/items', '::Controller:Order:Item#items')
			->setRequirement('orderID', '\d+');

		$router['ms.order']->add('ms.commerce.order.detail.view.addresses', 'view/{orderID}/addresses', '::Controller:Order:Address#addresses')
			->setRequirement('orderID', '\d+');

		$router['ms.order']->add('ms.commerce.order.detail.view.payments', 'view/{orderID}/payments', '::Controller:Order:Payment#payments')
			->setRequirement('orderID', '\d+');

		$router['ms.order']->add('ms.commerce.order.detail.view.dispatches', 'view/{orderID}/dispatches', '::Controller:Order:Dispatch#dispatches')
			->setRequirement('orderID', '\d+');

		$router['ms.order']->add('ms.commerce.order.detail.view.notes', 'view/{orderID}/notes', '::Controller:Order:Note#notes')
			->setRequirement('orderID', '\d+');
	}
}