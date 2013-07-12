<?php

namespace Message\Mothership\Commerce\User\Address;

use Message\User\User;
use Message\Cog\DB\Query;
use Message\Cog\DB\Result;

class Loader implements \Message\Mothership\Commerce\User\LoaderInterface
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
				user_address.address_id AS id,
				user_address.user_id AS userID,
				user_address.type AS type,
				user_address.name AS name,
				user_address.line_1,
				user_address.line_2,
				user_address.line_3,
				user_address.line_4,
				user_address.town,
				user_address.postcode,
				user_address.state_id AS stateID,
				state.name AS stateName,
				user_address.country_id AS countryID,
				country.name AS country,
				user_address.telephone
			FROM
				user_address
			JOIN
				country ON (country.country_id = user_address.country_id)
			LEFT JOIN
				state ON (
					user_address.state_id = state.state_id AND
					user_address.country_id = user_address.country_id
			)
			WHERE
				user_id = ?i',
			array(
				$user->id
			)
		);

		return $this->_loadAddresses($result);
	}

	protected function _loadAddresses(Result $result)
	{
		$addresses = $result->bindTo('Message\\Mothership\\Commerce\\User\\Address\\Address');
		foreach ($result as $key => $address) {
			$addresses[$key]->lines = array(
				$address->line_1,
				$address->line_2,
				$address->line_3,
				$address->line_4,
			);
		}

		return $addresses;
	}

}