<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

class OrderDispatch extends BaseTask
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
					despatch_id AS dispatch_id,
					order_id,
					IFNULL(
						UNIX_TIMESTAMP(despatch_timestamp),
						UNIX_TIMESTAMP(order_datetime)) AS created_at,
					NULL AS created_by,
					NULL AS updated_at,
					NULL AS updated_by,
					NULL AS shipped_at,
					NULL AS shipped_by,
					despatch_type_name AS method,
					despatch_code AS `code`,
					despatch_cost AS cost,
					despatch_weight AS weight_grams
				FROM
					order_despatch
				JOIN
					order_summary USING (order_id)
				JOIN
					order_despatch_type USING (despatch_type_id)';

		$result = $old->run($sql);
		$output= '';
		$new->add('TRUNCATE order_dispatch');

		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_dispatch
				(
					dispatch_id,
					order_id,
					created_at,
					created_by,
					updated_at,
					updated_by,
					shipped_at,
					shipped_by,
					method,
					code,
					cost,
					weight_grams
				)
				VALUES
				(
					:dispatch_id?,
					:order_id?,
					:created_at?,
					:created_by?,
					:updated_at?,
					:updated_by?,
					:shipped_at?,
					:shipped_by?,
					:method?,
					:code?,
					:cost?,
					:weight_grams?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successful</info>';
		}

		return $ouput;
    }
}