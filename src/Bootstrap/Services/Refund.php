<?php

namespace Message\Mothership\Commerce\Bootstrap\Services;

use Message\Mothership\Commerce;

use Message\Cog\Bootstrap\ServicesInterface;

class Refund implements ServicesInterface
{
	public function registerServices($services)
	{
		$services['refund.loader'] = $services->factory(function($c) {
			return new Commerce\Refund\Loader(
				$c['db.query'],
				$c['order.payment.methods']//,
				//$c['order.payment.loader']
			);
		});

		// $services['order.refund.create'] = $services->factory(function($c) {
		// 	return new Commerce\Order\Entity\Refund\Create($c['db.query'], $c['order.refund.loader'], $c['user.current']);
		// });

		// $services['order.refund.edit'] = $services->factory(function($c) {
		// 	return new Commerce\Order\Entity\Refund\Edit($c['db.query'], $c['order.refund.loader'], $c['user.current']);
		// });
	}
}
