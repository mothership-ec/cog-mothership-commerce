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
				'host'		=> $this->get('cfg')->porting->from->hostname,
				'user'		=> $this->get('cfg')->porting->from->user,
				'password' 	=> $this->get('cfg')->porting->from->password,
				'db'		=> $this->get('cfg')->porting->from->name,
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
				'host'		=> $this->get('cfg')->db->hostname,
				'user'		=> $this->get('cfg')->db->user,
				'password' 	=> $this->get('cfg')->db->password,
				'db'		=> $this->get('cfg')->db->name,
				'charset'	=> 'utf-8',
		));
	}
}