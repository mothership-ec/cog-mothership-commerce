<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Mothership\Commerce;

use Message\Cog\Bootstrap\EventsInterface;

class Events implements EventsInterface
{
	public function registerEvents($dispatcher)
	{
		$dispatcher->addSubscriber(new Commerce\Order\EventListener);
		$dispatcher->addSubscriber(new Commerce\Order\Entity\Item\EventListener);
		$dispatcher->addSubscriber(new Commerce\EventListener);

	}
}