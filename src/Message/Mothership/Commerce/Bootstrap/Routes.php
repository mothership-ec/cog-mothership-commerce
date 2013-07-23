<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.commerce']->setPrefix('/admin/');
		$router['ms.commerce']->add('ms.commerce.product.dashboard', 'product', '::Controller:Product:Dashboard#index');

		$router['ms.commerce']->add('ms.commerce.product.create.action', 'product/create', '::Controller:Product:Create#process')
			->setMethod('POST');

		$router['ms.commerce']->add('ms.commerce.product.create', 'product/create', '::Controller:Product:Create#index');

		$router['ms.commerce']->add('ms.commerce.product.edit.action', 'product/edit/{productID}', '::Controller:Product:Edit#process')
			->setRequirement('productID', '\d+')
			->setMethod('POST');

		$router['ms.commerce']->add('ms.commerce.product.edit', 'product/edit/{productID}', '::Controller:Product:Edit#index')
			->setRequirement('productID', '\d+');

		$router['ms.commerce']->add('ms.commerce.product.edit.unit.action', 'product/edit/{productID}/units', '::Controller:Product:Edit#units')
			->setRequirement('productID', '\d+')
			->setMethod('POST');

		$router['ms.commerce']->add('ms.commerce.product.edit.units', 'product/edit/{productID}/units', '::Controller:Product:Edit#units')
			->setRequirement('productID', '\d+');

		$router['ms.commerce']->add('ms.commerce.product.edit.stock', 'product/edit/{productID}/stock', '::Controller:Product:Edit#stock')
			->setRequirement('productID', '\d+');
	}
}