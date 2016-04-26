<?php

namespace Message\Mothership\Commerce\Product\Brand;

use Message\Cog\DB\Query;

/**
 * Simple class for loading brand options out of the database
 */
class Loader
{
	/**
	 * @var \Message\Cog\DB\Query
	 */
	protected $_query;


	public function getAll()
	{

		$result = $this->_query->run("SELECT DISTINCT brand FROM product");

		return $result->flatten();
	}
}
