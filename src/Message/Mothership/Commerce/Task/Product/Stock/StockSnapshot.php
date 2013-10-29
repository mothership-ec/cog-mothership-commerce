<?php

namespace Message\Mothership\Commerce\Task\Product\Stock;

use Message\Cog\Console\Task;

class StockSnapshot extends Task
{
    protected function configure()
    {
        // Run once a day at midnight
        $this->schedule('0 0 * * *');
    }

	public function process()
	{
		$conn = $this->getToConnection();

		$conn->add("
			INSERT INTO
				product_unit_stock_snapshot (
					unit_id,
					location,
					stock,
					created_at
				)
			SELECT
				unit_id,
				location,
				stock,
				UNIX_TIMESTAMP()
			FROM
				product_unit_stock

		");

		if ($conn->commit()) {
			$this->writeln('<info>Successfully cached a snapshot of current stock levels.</info>');
			return true;
		}

		$this->writeln('<error>Failed to cache snapshot of current stock levels.</error>');
		return false;
	}

	/**
	 * Gets the DB connection to port the data into.
	 *
	 * @return Connection Instance of the DB Connection.
	 */
	public function getToConnection()
	{
		return new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
				'host'		=> $this->get('cfg')->db->hostname,
				'user'		=> $this->get('cfg')->db->user,
				'password' 	=> $this->get('cfg')->db->pass,
				'db'		=> $this->get('cfg')->db->name,
				'charset'	=> 'utf-8',
		));
	}
}