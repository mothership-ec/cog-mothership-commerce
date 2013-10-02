<?php

namespace Message\Mothership\Commerce\Forex;

use Message\Cog\Console\Task\Task;

class FetchDataTask extends Task
{
	public function process()
	{
		$this->get('forex.feed')->fetch();

		return 'Forex data updated';
	}
}