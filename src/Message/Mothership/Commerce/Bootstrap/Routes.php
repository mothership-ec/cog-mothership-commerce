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



		$router['ms.order']->add('ms.commerce.order.details.view.index', 'view/{orderId}', '::Controller:Order:Details:Order#index')
			->setRequirement('orderId', '\d+');

		$router['ms.order']->add('ms.commerce.order.details.view.order-summary', 'view/{orderId}/order-summary', '::Controller:Order:Details:Order#orderSummary')
			->setRequirement('orderId', '\d+');

		$router['ms.order']->add('ms.commerce.order.details.view.items', 'view/{orderId}/items', '::Controller:Order:Details:Item#items')
			->setRequirement('orderId', '\d+');

		$router['ms.order']->add('ms.commerce.order.details.view.addresses', 'view/{orderId}/addresses', '::Controller:Order:Details:Address#addresses')
			->setRequirement('orderId', '\d+');

		$router['ms.order']->add('ms.commerce.order.details.view.payments', 'view/{orderId}/payments', '::Controller:Order:Details:Payment#payments')
			->setRequirement('orderId', '\d+');

		$router['ms.order']->add('ms.commerce.order.details.view.dispatches', 'view/{orderId}/dispatches', '::Controller:Order:Details:Dispatch#dispatches')
			->setRequirement('orderId', '\d+');

		$router['ms.order']->add('ms.commerce.order.details.view.notes', 'view/{orderId}/notes', '::Controller:Order:Details:Note#notes')
			->setRequirement('orderId', '\d+');
	}
}