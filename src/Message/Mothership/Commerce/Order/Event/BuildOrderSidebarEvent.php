<?php

namespace Message\Mothership\Commerce\Event;

use Message\Mothership\ControlPanel\Event\BuildMenuEvent;

class BuildOrderSidebarEvent extends BuildMenuEvent
{
	const BUILD_ORDER_SIDEBAR = 'ms.commerce.order.sidebar.build';
}