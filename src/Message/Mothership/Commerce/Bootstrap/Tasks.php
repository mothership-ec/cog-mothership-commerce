<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Cog\Bootstrap\TasksInterface;
use Message\Mothership\Commerce\Task;

class Tasks implements TasksInterface
{
    public function registerTasks($tasks)
    {
        $tasks->add(new Task\Porting\OrderSummary('commerce:porting:port_order_summary'), 'Ports order_summary from pre mothership');
        $tasks->add(new Task\Porting\OrderShipping('commerce:porting:port_shipping'), 'Ports order_shipping from pre mothership');
        $tasks->add(new Task\Porting\OrderAddress('commerce:porting:port_order_address'), 'Ports order_address from pre mothership');
        $tasks->add(new Task\Porting\OrderItem('commerce:porting:port_order_item'), 'Ports order_item from pre mothership');
        $tasks->add(new Task\Porting\OrderRefund('commerce:porting:port_order_refund'), 'Ports order_refund from pre mothership');
        $tasks->add(new Task\Porting\OrderRepair('commerce:porting:port_order_repair'), 'Ports order_repair from pre mothership');
        $tasks->add(new Task\Porting\OrderPayment('commerce:porting:port_order_payment'), 'Ports order_payment from pre mothership');
        $tasks->add(new Task\Porting\OrderNote('commerce:porting:port_order_note'), 'Ports order_note from pre mothership');
        $tasks->add(new Task\Porting\OrderMetadata('commerce:porting:port_order_metadata'), 'Ports order_note from pre mothership');
        $tasks->add(new Task\Porting\OrderItemStatus('commerce:porting:port_order_item_status'), 'Ports order_item_status from pre mothership');
        $tasks->add(new Task\Porting\OrderItemReturn('commerce:porting:port_order_item_return'), 'Ports order_item_return from pre mothership');
        $tasks->add(new Task\Porting\OrderItemPersonalisation('commerce:porting:port_order_item_personalisation'), 'Ports order_item_personalisation from pre mothership');
        $tasks->add(new Task\Porting\OrderItemDiscount('commerce:porting:port_order_item_discount'), 'Ports order_item_discount from pre mothership');
        $tasks->add(new Task\Porting\OrderItemDispatch('commerce:porting:port_order_item_dispatch'), 'Ports order_item_dispatch from pre mothership');
        $tasks->add(new Task\Porting\OrderDispatch('commerce:porting:port_order_dispatch'), 'Ports order_dispatch from pre mothership');
        $tasks->add(new Task\Porting\OrderDiscount('commerce:porting:port_order_discount'), 'Ports order_discount from pre mothership');

        $tasks->add(new Task\Porting\Products('commerce:porting:port_products'), 'Ports catalogue and catalogue_info from pre mothership');

    }
}