<?php

namespace Message\Mothership\Commerce\User\Address;

use Message\User\UserInterface;
use Message\Cog\DB\Query;
use Message\Cog\DB\Result;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\Cog\Location\CountryList;
use Message\Cog\Location\StateList;

class Loader implements \Message\Mothership\Commerce\User\LoaderInterface
{
	protected $_query;
	protected $_countries;
	protected $_states;

	public function __construct(Query $query, CountryList $countries, StateList $states)
	{
		$this->_query = $query;
		$this->_countries = $countries;
		$this->_states = $states;
	}

	/**
	 * Load all addresses for a given user
	 *
	 * @param  User   $user User to return address for
	 *
	 * @return array|Address an array of, or singular Address object
	 */
	public function getByUser(UserInterface $user)
	{
		$result = $this->_query->run('
			SELECT
				address_id
			FROM
				user_address
			WHERE
				user_id = ?in
		', array(
			$user->id
		)
		);

		return count($result) ? $this->_loadAddresses($result->flatten(), true) : false;
	}

	/**
	 * Get an address by user and by the address type
	 *
	 * @param  User   $user 	User to load address for
	 * @param  string $type 	billing or delivery
	 *
	 * @return Address|false    Loaded address
	 */
	public function getByUserAndType(UserInterface $user, $type)
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
	public function getByID($addressID)
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
	protected function _loadAddresses($addressIDs, $alwaysReturnArray = false)
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
				user_address.country_id AS countryID,
				user_address.telephone,
				user_address.created_at,
				user_address.created_by,
				user_address.updated_at,
				user_address.updated_by
			FROM
				user_address
			WHERE
				user_address.address_id IN (?ij)
			', array(
				(array) $addressIDs
			)
		);

		if (0 === count($result)) {
			return ($alwaysReturnArray? array() : false);
		}

		return $this->_buildAddresses($result, $alwaysReturnArray);
	}

	/**
	 * Handles binding of the results to the Address objects and building authorship
	 *
	 * @param  Result $result Result object to gather results from
	 *
	 * @return array|Address|false an array of Address objects or a singular Address object
	 */
	protected function _buildAddresses(Result $result, $alwaysReturnArray)
	{
		$addresses = $result->bindTo('Message\\Mothership\\Commerce\\User\\Address\\Address');
		foreach ($result as $key => $address) {

			$addresses[$key]->lines = array(
				1 => $address->line_1 ?: null,
				2 => $address->line_2 ?: null,
				3 => $address->line_3 ?: null,
				4 => $address->line_4 ?: null,
			);

			$addresses[$key]->country = $this->_countries->getByID($address->countryID);

			if ($address->stateID) {
				$addresses[$key]->state = $this->_states->getByID($address->countryID, $address->stateID);
			}

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

		return count($addresses) != 1 || $alwaysReturnArray ? $addresses : array_shift($addresses);
	}

}