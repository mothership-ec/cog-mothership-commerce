<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.product']->setParent('ms.cp')->setPrefix('/product');
		$router['ms.product']->add('ms.commerce.product.dashboard', '', '::Controller:Product:Dashboard#index');

		$router['ms.product']->add('ms.commerce.product.create.action', 'create', '::Controller:Product:Create#process')
			->setMethod('POST');

		$router['ms.product']->add('ms.commerce.product.create', 'create', '::Controller:Product:Create#index');

		$router['ms.product']->add('ms.commerce.product.edit.action', 'edit/{productID}', '::Controller:Product:Edit#process')
			->setRequirement('productID', '\d+')
			->setMethod('POST');

		$router['ms.product']->add('ms.commerce.product.edit', 'edit/{productID}', '::Controller:Product:Edit#index')
			->setRequirement('productID', '\d+');

		$router['ms.commerce']->add('ms.commerce.product.edit.units.action', 'product/edit/{productID}/units', '::Controller:Product:Edit#unitProcess')
			->setRequirement('productID', '\d+')
			->setMethod('POST');

		$router['ms.commerce']->add('ms.commerce.product.edit.units.create.action', 'product/edit/{productID}/units/create', '::Controller:Product:Edit#addUnitProccess')
			->setRequirement('productID', '\d+')
			->setMethod('POST');

		$router['ms.product']->add('ms.commerce.product.edit.units', 'edit/{productID}/units', '::Controller:Product:Edit#units')
			->setRequirement('productID', '\d+');

		$router['ms.product']->add('ms.commerce.product.edit.stock', 'edit/{productID}/stock', '::Controller:Product:Edit#stock')
			->setRequirement('productID', '\d+');
	}
}