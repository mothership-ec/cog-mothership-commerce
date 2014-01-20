<?php

namespace Message\Mothership\Commerce\User\CommerceData;

use Message\User\User;
use Message\Cog\DB\Query;
use Message\Cog\DB\Result;

class Loader
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function getByUser(User $user)
	{
		$result = $this->_query->run(
			'SELECT
				order_id,
				created_at
			FROM
				order_summary
			WHERE
				user_id = ?i
			ORDER BY
				created_at ASC',
			array(
				$user->id
			)
		);

		$data = new Data;
		$data->numberOfOrders = count($result);
		$last = $result->last();
		$data->lastOrderDate = new \DateTime(date('c',$last->created_at));
		$data->firstOrderDate = new \DateTime(date('c',$result->first()->created_at));

		return $data;
	}

}