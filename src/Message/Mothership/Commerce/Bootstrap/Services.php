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
				'addresses' => $c['order.address.loader'],
				'items'     => $c['order.item.loader'],
				'payments'  => $c['order.payment.loader'],
				'notes'     => $c['order.note.loader'],
			);
		};

		// Order decorators
		$services['order.loader'] = function($c) {
			return new Commerce\Order\Loader($c['db.query'], $c['user.loader'], $c['order.entities']);
		};
		$services['order.create'] = function($c) {
			return new Commerce\Order\Create(
				$c['db.transaction'],
				$c['order.loader'],
				$c['event.dispatcher'],
				$c['user.current'],
				array(
					'addresses' => $c['order.address.create'],
					'items'     => $c['order.item.create'],
				)
			);
		};

		// Order entity loaders
		$services['order.address.loader'] = function($c) {
			return new Commerce\Order\Entity\Address\Loader($c['db.query']);
		};

		$services['order.item.loader'] = function($c) {
			return new Commerce\Order\Entity\Item\Loader($c['db.query']);
		};

		$services['order.payment.loader'] = function($c) {
			return new Commerce\Order\Entity\Payment\Loader($c['db.query'], $c['order.payment.methods']);
		};

		$services['order.note.loader'] = function($c) {
			return new Commerce\Order\Entity\Note\Loader($c['db.query']);
		};

		// Order entity creators
		$services['order.address.create'] = function($c) {
			return new Commerce\Order\Entity\Address\Create($c['db.query']);
		};

		$services['order.item.create'] = function($c) {
			return new Commerce\Order\Entity\Item\Create($c['db.transaction'], $c['user.current']);
		};

		// Available payment & despatch methods
		$services['order.payment.methods'] = $services->share(function($c) {
			return new Commerce\Order\Entity\Payment\MethodCollection(array(
				new Commerce\Order\Entity\Payment\Method\Card,
				new Commerce\Order\Entity\Payment\Method\Cash,
				new Commerce\Order\Entity\Payment\Method\Cheque,
			));
		});
	}
}