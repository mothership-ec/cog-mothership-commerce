<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use Message\Cog\Console\Task\Task;

/**
 * Process pending stock replenished notifications. This emails the notification
 * to the related email address and sets it to notified.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class ProcessPendingTask extends Task
{
	protected function configure()
    {
        // Run every hour
        $this->schedule('0 * * * *');
    }

	public function process()
	{
		$pending = $this->get('stock.notification.replenished.loader')->getPending();

		if (! $pending) {
			$this->writeln("<comment>No notifications pending</comment>");
			return;
		}

		$userNotifications = array();

		// Group the notifications by user
		foreach ($pending as $notification) {
			$userNotifications[$notification->email][] = $notification;
		}

		$notified = 0;

		foreach ($userNotifications as $notifications) {

			$factory = $this->get('mail.factory.stock.notification.replenished')
				->set('notifications', $notifications)
				->set('email', $notifications[0]->email);

			if ($this->get('mail.dispatcher')->send($factory->getMessage())) {
				foreach ($notifications as $notification) {
					$this->get('stock.notification.replenished.edit')->setNotified($notification);
				}

				$notified++;
			}
		}

		$this->writeln(sprintf("<comment>%s users notified of %s users found</comment>", $notified, count($userNotifications)));
	}

}