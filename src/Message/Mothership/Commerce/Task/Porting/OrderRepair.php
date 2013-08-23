<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

class OrderRepair extends BaseTask
{

    public function process()
    {
        $uwOld = new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
				'host'		=> '127.0.0.1',
				'user'		=> 'root',
				'password' 	=> 'chelsea',
				'db'		=> 'uniform_wares',
				'charset'	=> 'utf-8',
		));


		$uwNew = new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
				'host'		=> '127.0.0.1',
				'user'		=> 'root',
				'password' 	=> 'chelsea',
				'db'		=> 'mothership_cms',
				'charset'	=> 'utf-8',
		));

		$new = new \Message\Cog\DB\Transaction($uwNew);
		$old = new \Message\Cog\DB\Query($uwOld);

		$sql = 'SELECT
					repair_id,
					order_id,
					order_item_id,
					catalogue_id,
					product_name,
					repair_notes,
					repair_purchase_date,
					repair_retailer,
					repair_faulty
				FROM
					order_repair';

		$result = $old->run($sql);
		$output= '';
		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_repair
				(
					repair_id,
					order_id,
					order_item_id,
					catalogue_id,
					product_name,
					repair_notes,
					repair_purchase_date,
					repair_retailer,
					repair_faulty
				)
				VALUES
				(
					:repair_id?,
					:order_id?,
					:order_item_id?,
					:catalogue_id?,
					:product_name?,
					:repair_notes?,
					:repair_purchase_date?,
					:repair_retailer?,
					:repair_faulty?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successfulo</info>';
		}

		return $ouput;
    }
}