<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Mothership\Commerce;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$services['order'] = function($c) {
			return new Commerce\Order\Order($c['order.entities']);
		};

		$services['order.entities'] = function($c) {
			return array(
				'items' => $c['order.item.loader'],
			);
		};

		// Order decorators
		$services['order.loader'] = function($c) {
			return new Commerce\Order\Loader($c['db.query'], $c['user.loader'], $c['order.entities']);
		};

		// Order entity loaders
		$services['order.item.loader'] = function($c) {
			return new Commerce\Order\Entity\Item\Loader($c['db.query']);
		};

		$services['order.payment.loader'] = function($c) {
			return new Commerce\Order\Entity\Payment\Loader($c['db.query']);
		};
	}
}