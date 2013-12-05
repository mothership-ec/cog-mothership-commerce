<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use Message\Cog\Console\Task\Task;

class ProcessPendingTask extends Task
{
	protected function configure()
    {
        // Run every hour
        $this->schedule('0 * * * *');
    }

	public function run()
	{
		$pending = $this->get('stock.notification.replenished.loader')->getPending();

		foreach ($pending as $notification) {
			$factory = $this->get('mail.factory.stock.notification.replenished')
				->set('notification', $notification);

			if ($this->get('mail.dispatcher')->send($factory->getMessage())) {
				$this->get('stock.notification.replenished.edit')->setNotified($notification);
			}
		}
	}

}