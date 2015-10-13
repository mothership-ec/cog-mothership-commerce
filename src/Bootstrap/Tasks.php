<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Cog\Bootstrap\TasksInterface;
use Message\Mothership\Commerce\Task;
use Message\Mothership\Commerce\Forex;
use Message\Mothership\Commerce\Product;

class Tasks implements TasksInterface
{
	public function registerTasks($tasks)
	{
		$tasks->add(new Task\Stock\Barcode('commerce:stock:from_barcodes'), 'Updates stock from a text file of barcodes');

		$tasks->add(new Forex\FetchDataTask('commerce:forex:fetch'), 'Fetches the lastest forex data from the feed');

		$tasks->add(new Product\Stock\StockSnapshot('commerce:stock:snapshot'), 'Creates a snapshot of current stock levels');

		$tasks->add(new Task\Order\FlushOrders('commerce:flush_orders'), 'Completes orders that are stuck in fulfillment');

		$tasks->add(new Product\Barcode\GenerateTask('commerce:barcode:generate'), 'Creates barcode images for all units in the database');

		$tasks->add(new Task\Product\DeleteOptionlessUnits('commerce:delete_optionless_units'), 'Marks units with no options as deleted');
	}
}