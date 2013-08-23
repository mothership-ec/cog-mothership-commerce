<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

class OrderRefund extends BaseTask
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
					order_refund.refund_id,
					order_refund.order_id,
					MAX(payment_id) AS payment_id,
					return_id,
					UNIX_TIMESTAMP(return_datetime) AS created_at,
					NULL AS created_by,
					payment_type_name AS method,
					refund_amount AS amount,
					refund_reason_name AS reason,
					refund_reference AS reference
				FROM
					order_refund
				JOIN
					order_item_return USING (refund_id)
				JOIN
					order_payment_type USING (payment_type_id)
				JOIN
					order_refund_reason USING (refund_reason_id)
				LEFT JOIN
					order_payment ON (order_payment.order_id = order_refund.order_id AND payment_amount = refund_amount)
				GROUP BY
					order_refund.refund_id';

		$result = $old->run($sql);
		$output= '';
		$new->add('TRUNCATE order_refund');

		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_refund
				(
					refund_id,
					order_id,
					payment_id,
					return_id,
					created_at,
					created_by,
					method,
					amount,
					reason,
					reference
				)
				VALUES
				(
					:refund_id?,
					:order_id?,
					:payment_id?,
					:return_id?,
					:created_at?,
					:created_by?,
					:method?,
					:amount?,
					:reason?,
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