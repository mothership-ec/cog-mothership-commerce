<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

class OrderItemReturn extends BaseTask
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
					return_id,
					order_item_return.order_id,
					order_item_return.item_id,
					UNIX_TIMESTAMP(return_datetime) AS created_at,
					user_id AS created_by,
					UNIX_TIMESTAMP(package_received_date) AS updated_at,
					NULL AS updated_by,
					IF (return_status_id = 55, UNIX_TIMESTAMP(package_received_date), NULL) AS completed_at,
					NULL AS completed_by,
					return_exchange_item_id AS exchange_item_id,
					CASE return_status_id
						WHEN 51 THEN 2000
						WHEN 52 THEN 2300
						WHEN 53 THEN 2400
						WHEN 54 THEN 2500
						WHEN 55 THEN 3000
					END AS status_id,
					return_reason_name AS reason,
					return_resolution_name AS resolution,
					IFNULL(balancing_payment,0) AS balance,
					IFNULL(balancing_payment,0) AS calculated_balance,
					item_price - IFNULL(item_discount,0) AS returned_value,
					return_destination_id AS return_to_stock_location_id
				FROM
					order_item_return
				JOIN order_item ON (order_item.item_id = order_item_return.item_id)
				JOIN order_summary ON (order_item_return.order_id = order_summary.order_id)
				JOIN order_return_reason USING (return_reason_id)
				JOIN order_return_resolution USING (return_resolution_id)';

		$result = $old->run($sql);
		$output= '';
		$new->add('TRUNCATE order_item_return');

		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_item_return
				(
					return_id,
					order_id,
					item_id,
					created_at,
					created_by,
					updated_at,
					updated_by,
					completed_at,
					completed_by,
					exchange_item_id,
					status_id,
					reason,
					resolution,
					balance,
					calculated_balance,
					returned_value,
					return_to_stock_location_id
				)
				VALUES
				(
					:return_id?,
					:order_id?,
					:item_id?,
					:created_at?,
					:created_by?,
					:updated_at?,
					:updated_by?,
					:completed_at?,
					:completed_by?,
					:exchange_item_id?,
					:status_id?,
					:reason?,
					:resolution?,
					:balance?,
					:calculated_balance?,
					:returned_value?,
					:return_to_stock_location_id?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successful</info>';
		}

		return $ouput;
    }
}