<?php

namespace Message\Mothership\Commerce\User\Address;

use Message\User\User;

use Message\Cog\DB\Query;

use Message\Cog\ValueObject\DateTimeImmutable;

class Delete
{
	protected $_query;
	protected $_user;

	public function __construct(Query $query)
	{
		$this->_query  = $query;
	}

	/**
	 * Save the new Address object into the DB and return it
	 *
	 * @param  Address $address The address object to be saved
	 *
	 * @return false|Address 	The loaded Address object or false if it wasn't inserted
	 */
	public function delete(Address $address)
	{
		$result = $this->_query->run(
			'DELETE FROM
				user_address
			WHERE
				address_id = ?i
			', array(
				$address->id
			)
		);

		return (bool) $result->affected();
	}
}