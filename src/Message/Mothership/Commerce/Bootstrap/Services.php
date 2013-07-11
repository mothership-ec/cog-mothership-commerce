<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Mothership\Commerce;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		// Order entity loaders
		$services['order.item.loader'] = function($c) {
			return new Commerce\Order\Entity\Item\Loader($c['db.query']);
		};

		$services['order.payment.loader'] = function($c) {
			return new Commerce\Order\Entity\Payment\Loader($c['db.query']);
		};
	}
}