<?php

namespace Message\Mothership\Commerce\User\Address;

use Message\User\User;
use Message\Cog\DB\Query;
use Message\Cog\DB\Result;
use Message\Cog\ValueObject\DateTimeImmutable;

class Loader implements \Message\Mothership\Commerce\User\LoaderInterface
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	/**
	 * Load all addresses for a given user
	 *
	 * @param  User   $user User to return address for
	 *
	 * @return array|Address an array of, or singular Address object
	 */
	public function getByUser(User $user)
	{
		$result = $this->_query->run('
			SELECT
				address_id
			FROM
				user_address
			WHERE
				user_id = ?i
		', $user->id);

		return count($result) ? $this->_loadAddresses($result->flatten()) : false;
	}

	/**
	 * Get an address by user and by the address type
	 *
	 * @param  User   $user 	User to load address for
	 * @param  string $type 	billing or delivery
	 *
	 * @return Address|false    Loaded address
	 */
	public function getByUserAndType(User $user, $type)
	{
		$results = $this->getByUser($user);

		if ($results) {
			foreach ($results as $address) {
				if ($address->type == $type) {
					return $address;
				}
			}
		}

		return false;
	}

	/**
	 * Get an Address object by the address_id
	 *
	 * @param  int $addressID 	addressID to return
	 *
	 * @return Address|false    Address object or false if not found
	 */
	public function getByAddressID($addressID)
	{
		return $this->_loadAddresses($addressID);
	}

	/**
	 * The actualy retreval from the DB comes from
	 *
	 * @param  array|int $addressIDs address objects to be loaded
	 *
	 * @return array|Address|false 	singular or array of Address objects or false if none found
	 */
	protected function _loadAddresses($addressIDs)
	{
		$result = $this->_query->run(
			'SELECT
				user_address.address_id AS id,
				user_address.user_id AS userID,
				user_address.type AS type,
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
				user_address.telephone,
				user_address.created_at,
				user_address.created_by,
				user_address.updated_at,
				user_address.updated_by
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
				user_address.address_id IN (?ij)
			', array(
				(array) $addressIDs
			)
		);

		if (0 === count($result)) {
			return false;
		}

		return $this->_buildAddresses($result);
	}

	/**
	 * Handles binding of the results to the Address objects and building authorship
	 *
	 * @param  Result $result Result object to gather results from
	 *
	 * @return array|Address|false an array of Address objects or a singular Address object
	 */
	protected function _buildAddresses(Result $result)
	{
		$addresses = $result->bindTo('Message\\Mothership\\Commerce\\User\\Address\\Address');
		foreach ($result as $key => $address) {

			$addresses[$key]->lines = array(
				1 => $address->line_1 ?: null,
				2 => $address->line_2 ?: null,
				3 => $address->line_3 ?: null,
				4 => $address->line_4 ?: null,
			);

			$addresses[$key]->authorship->create(
				new DateTimeImmutable(date('c', $address->created_at)),
				$address->created_by
			);

			if ($address->updated_at) {
				$addresses[$key]->authorship->update(
					new DateTimeImmutable(date('c', $address->updated_at)),
					$address->updated_by
				);
			}
		}

		return count($addresses) == 1 ? array_shift($addresses) : $addresses;
	}

}