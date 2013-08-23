<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

class OrderMetadata extends BaseTask
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
					metadata_key AS `key`,
					metadata_value AS `value`
				FROM
					order_metadata';

		$result = $old->run($sql);
		$output= '';
		$new->add('TRUNCATE order_metadata');

		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_metadata
				(
					order_id,
					`key`,
					`value`
				)
				VALUES
				(
					:order_id?,
					:key?,
					:value?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successful</info>';
		}

		return $ouput;
    }
}