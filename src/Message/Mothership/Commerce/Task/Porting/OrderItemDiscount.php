<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

class OrderItemDiscount extends BaseTask
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
					item_id,
					campaign_id AS discount_id,
					item_discount AS amount
				FROM
					order_item
				JOIN
					order_discount USING (order_id)
				JOIN
					val_campaign ON (val_campaign.campaign_code = order_discount.discount_id)
				WHERE item_discount > 0
				GROUP BY item_id';

		$result = $old->run($sql);
		$output= '';
		$new->add('TRUNCATE order_item_discount');

		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_item_discount
				(
					item_id,
					discount_id,
					amount
				)
				VALUES
				(
					:item_id?,
					:discount_id?,
					:amount?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successful</info>';
		}

		return $ouput;
    }
}