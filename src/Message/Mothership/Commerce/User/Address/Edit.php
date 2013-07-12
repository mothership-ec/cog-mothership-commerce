<?php

namespace Message\Mothership\Commerce\User\Address;

class Edit
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function save(Address $address)
	{
		# code...
	}
}