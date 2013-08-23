<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Cog\Bootstrap\TasksInterface;
use Message\Mothership\Commerce\Task;

class Tasks implements TasksInterface
{
    public function registerTasks($tasks)
    {
        $tasks->add(new Task\Porting\OrderSummary('commerce:porting:port_orders'), 'Ports order_summary from pre mothership');
        $tasks->add(new Task\Porting\OrderShipping('commerce:porting:port_shipping'), 'Ports order_shipping from pre mothership');
        $tasks->add(new Task\Porting\OrderAddress('commerce:porting:port_order_address'), 'Ports order_address from pre mothership');
        $tasks->add(new Task\Porting\OrderItem('commerce:porting:port_order_item'), 'Ports order_item from pre mothership');
        $tasks->add(new Task\Porting\OrderRefund('commerce:porting:port_order_refund'), 'Ports order_refund from pre mothership');
        $tasks->add(new Task\Porting\OrderRepair('commerce:porting:port_order_repair'), 'Ports order_repair from pre mothership');
    }
}