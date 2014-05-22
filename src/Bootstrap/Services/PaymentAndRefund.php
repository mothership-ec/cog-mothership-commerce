<?php

namespace Message\Mothership\Commerce\Bootstrap\Services;

use Message\Mothership\Commerce;

use Message\Cog\Bootstrap\ServicesInterface;

class PaymentAndRefund implements ServicesInterface
{
	public function registerServices($services)
	{
		// @todo rename this to payment.methods in next major version
		$services['order.payment.methods'] = function($c) {
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
				$c['order.payment.methods']
			);
		});

		$services['payment.create'] = $services->factory(function($c) {
			return new Commerce\Payment\Create(
				$c['db.transaction'],
				$c['payment.loader'],
				$c['event.dispatcher'],
				$c['user.current']
			);
		});

		$services['payment.delete'] = $services->factory(function($c) {
			return new Commerce\Payment\Delete($c['db.query'], $c['user.current']);
		});

		$services['refund.loader'] = $services->factory(function($c) {
			return new Commerce\Refund\Loader(
				$c['db.query'],
				$c['order.payment.methods'],
				$c['payment.loader']
			);
		});

		$services['refund.create'] = $services->factory(function($c) {
			return new Commerce\Refund\Create(
				$c['db.transaction'],
				$c['refund.loader'],
				$c['event.dispatcher'],
				$c['user.current']
			);
		});

		$services['refund.delete'] = $services->factory(function($c) {
			return new Commerce\Refund\Delete($c['db.query'], $c['user.current']);
		});
	}
}
