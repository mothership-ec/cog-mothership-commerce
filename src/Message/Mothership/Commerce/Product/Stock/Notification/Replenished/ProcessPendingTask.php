<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

class ProcessPendingTask {

	public function run()
	{
		$pending = $this->get('stock.notification.replenished.loader')->getPending();

		foreach ($pending as $notification) {
			$message = $this->get('mail.factory.stock.notification.replenished');

			$factory = $this->get('mail.factory.order.note.notification')
				->set('notification', $notification);

			$this->get('mail.dispatcher')->send($factory->getMessage());

			$this->get('stock.notification.replenished.edit')->setNotified($notification);
		}
	}

}