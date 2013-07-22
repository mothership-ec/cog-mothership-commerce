<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.commerce']->setPrefix('/admin/product/');

		$router['ms.commerce']->add('ms.commerce.product.create.action', 'create', '::Controller:Product:Create#process')
			->setMethod('POST');

		$router['ms.commerce']->add('ms.commerce.product.create', 'create', '::Controller:Product:Create#index');

		$router['ms.commerce']->add('ms.commerce.product.edit.action', 'edit/{productID}', '::Controller:Product:Edit#process')
			->setRequirement('productID', '\d+')
			->setMethod('POST');

		$router['ms.commerce']->add('ms.commerce.product.edit', 'edit/{productID}', '::Controller:Product:Edit#index')
			->setRequirement('productID', '\d+');
	}
}