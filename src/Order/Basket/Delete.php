<?php

namespace Message\Mothership\Commerce\Order\Basket;

use Message\Cog\DB;
use Message\Mothership\Commerce\Order\Order;

class Delete {

	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	public function delete($basketID)
	{
		$result = $this->_query->run('
			DELETE FROM
				basket
			WHERE
				basket_id = ?i
		', $basketID);

		return $basketID;
	}
}