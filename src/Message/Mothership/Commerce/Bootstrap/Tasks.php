<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Cog\Bootstrap\TasksInterface;
use Message\Mothership\Commerce\Task;

class Tasks implements TasksInterface
{
    public function registerTasks($tasks)
    {
        $tasks->add(new Task\Porting\OrderSummary('commerce:porting:port_orders'), 'Ports order_summary from pre mothership');
        $tasks->add(new Task\Porting\OrderShipping('commerce:porting:port_shipping'), 'Ports order_sshipping from pre mothership');
    }
}