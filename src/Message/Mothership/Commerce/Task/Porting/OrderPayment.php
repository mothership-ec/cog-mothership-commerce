<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

class OrderPayment extends BaseTask
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
					payment_id,
					order_id,
					NULL AS return_id,
					UNIX_TIMESTAMP(payment_datetime) AS created_at,
					NULL AS created_by,
					payment_type_name AS method,
					payment_amount AS amount,
					payment_reference AS reference
				FROM
					order_payment
				JOIN order_payment_type USING (payment_type_id)';

		$result = $old->run($sql);
		$output= '';
		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_payment
				(
					payment_id,
					order_id,
					return_id,
					created_at,
					created_by,
					method,
					amount,
					reference
				)
				VALUES
				(
					:payment_id?,
					:order_id?,
					:return_id?,
					:created_at?,
					:created_by?,
					:method?,
					:amount?,
					:reference?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successful</info>';
		}

		return $ouput;
    }
}