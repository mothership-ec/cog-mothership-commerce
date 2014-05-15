<?php

namespace Message\Mothership\Commerce\Bootstrap\Services;

use Message\Mothership\Commerce;

use Message\Cog\Bootstrap\ServicesInterface;

class PaymentAndRefund implements ServicesInterface
{
	public function registerServices($services)
	{
		$services['payment.methods'] = function($c) {
			return new Commerce\Order\Entity\Payment\MethodCollection(array(
				new Commerce\Payment\Method\Card,
				new Commerce\Payment\Method\Cash,
				new Commerce\Payment\Method\Cheque,
				new Commerce\Payment\Method\Manual,

				new Commerce\Payment\Method\Paypal,
				new Commerce\Payment\Method\CashOnDelivery,
				new Commerce\Payment\Method\PaymentOnPickup,
			));
		};

		$services['payment.loader'] = $services->factory(function($c) {
			return new Commerce\Payment\Loader(
				$c['db.query'],
				$c['payment.methods']
			);
		});

		$services['refund.loader'] = $services->factory(function($c) {
			return new Commerce\Refund\Loader(
				$c['db.query'],
				$c['payment.methods'],
				$c['payment.loader']
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
