<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

class OrderItemStatus extends BaseTask
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
					order_id,
					item_id,
					CASE status_id
						WHEN 0 THEN 0
						WHEN NULL THEN 0
						WHEN 1 THEN 100
						WHEN 2 THEN 200
						WHEN 3 THEN 300
						WHEN 4 THEN 400
						WHEN 6 THEN 1000
						WHEN 8 THEN 2000
						WHEN 7 THEN 2300
						WHEN 9 THEN 2800
						WHEN 10 THEN 2600
						WHEN 99 THEN 2400
						WHEN -2 THEN -300
						WHEN -1 THEN -100
					END AS status_code,
					UNIX_TIMESTAMP(status_datetime) AS created_at,
					staff_id AS created_by
				FROM
					order_item_status';

		$result = $old->run($sql);
		$output= '';
		$new->add('TRUNCATE order_item_status');

		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_item_status
				(
					order_id,
					item_id,
					status_code,
					created_at,
					created_by
				)
				VALUES
				(
					:order_id?,
					:item_id?,
					:status_code?,
					:created_at?,
					:created_by?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successful</info>';
		}

		return $ouput;
    }
}