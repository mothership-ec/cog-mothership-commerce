<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Mothership\Commerce;

use Message\Cog\Bootstrap\CommandsInterface;

class Commands implements CommandsInterface
{
	public function registerCommands($commands)
	{
		$commands->add(new Commerce\Order\Command\StatusList);
	}
}