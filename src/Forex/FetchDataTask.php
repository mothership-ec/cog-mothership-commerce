<?php

namespace Message\Mothership\Commerce\Forex;

use Message\Cog\Console\Task\Task;

class FetchDataTask extends Task
{
	protected function configure()
    {
        // Run once a day at midnight
        $this->schedule('0 0 * * *');
    }

	public function process()
	{
		$this->output('mail')->enable();
		$this->output('mail')->getMessage()->setTo('laurence@message.co.uk');

		$this->get('forex.feed')->fetch();

		return 'Forex data updated';
	}
}