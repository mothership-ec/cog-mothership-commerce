<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

class OrderItemPersonalisation extends BaseTask
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
					sender_name,
					recipient_name,
					recipient_email,
					message
				FROM
					order_item_personalisation';

		$result = $old->run($sql);
		$output= '';
		$new->add('TRUNCATE order_item_personalisation');

		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_item_personalisation
				(
					item_id,
					sender_name,
					recipient_name,
					recipient_email,
					message
				)
				VALUES
				(
					:item_id?,
					:sender_name?,
					:recipient_name?,
					:recipient_email?,
					:message?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successful</info>';
		}

		return $ouput;
    }
}