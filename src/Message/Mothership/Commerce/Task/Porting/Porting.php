<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

abstract class Porting extends BaseTask
{
	/**
	 * Gets the DB connection to port the data from
	 *
	 * @return Connection 		instance of the DB Connection
	 */
	public function getFromConnection()
	{
        return new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
				'host'		=> '127.0.0.1',
				'user'		=> 'root',
				'password' 	=> 'chelsea',
				'db'		=> 'uniform_wares',
				'charset'	=> 'utf-8',
		));
	}

	/**
	 * Gets the DB connection to port the data into
	 *
	 * @return Connection 		instance of the DB Connection
	 */
	public function getToConnection()
	{

		return new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
				'host'		=> '127.0.0.1',
				'user'		=> 'root',
				'password' 	=> 'chelsea',
				'db'		=> 'mothership_cms',
				'charset'	=> 'utf-8',
		));
	}
}