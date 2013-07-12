<?php

namespace Message\Mothership\Commerce\User\Address;

use Message\User\User;
use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;

class Edit
{
	protected $_query;
	protected $_user;

	public function __construct(Query $query, User $user)
	{
		$this->_query = $query;
		$this->_user  = $user;
	}

	public function save(Address $address)
	{
		$date = new DateTimeImmutable;
		$address->authorship->update($date, $this->_user->id);

		$result = $this->_query->run(
			'UPDATE
				user_address
			SET
				name       = :name?s,
				line_1     = :line_1?s,
				line_2     = :line_2?s,
				line_3     = :line_3?s,
				line_4     = :line_4?s,
				town       = :town?s,
				state_id   = :state_id?s,
				country_id = :country_id?s,
				postcode   = :postcode?s,
				telephone  = :telephone?s,
				updated_at = :updated_at?d,
				updated_by  = :updated_by?i
			WHERE
				address_id = :addressID?i
			', array(
				'addressID'	 => $address->id,
				'name'       => $address->name,
				'line_1'     => $address->lines[0],
				'line_2'     => $address->lines[1],
				'line_3'     => $address->lines[2],
				'line_4'     => $address->lines[3],
				'town'       => $address->town,
				'state_id'   => $address->stateID,
				'country_id' => $address->countryID,
				'postcode'   => $address->postcode,
				'telephone'  => $address->telephone,
				'updated_at' => $address->authorship->updatedAt(),
				'updated_by'  => $address->authorship->updatedBy(),
			)
		);

		return $address;
	}
}