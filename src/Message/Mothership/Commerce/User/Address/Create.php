<?php

namespace Message\Mothership\Commerce\User\Address;

use Message\User\UserInterface;

use Message\Cog\DB\Query;

use Message\Cog\ValueObject\DateTimeImmutable;

class Create
{
	protected $_query;
	protected $_currentUser;
	protected $_loader;

	public function __construct(Query $query, Loader $loader, UserInterface $user)
	{
		$this->_query  = $query;
		$this->_loader = $loader;
		$this->_currentUser   = $user;
	}

	/**
	 * Save the new Address object into the DB and return it
	 *
	 * @param  Address $address The address object to be saved
	 *
	 * @return false|Address 	The loaded rAddress object or false if it wasn't inserted
	 */
	public function create(Address $address)
	{
		// Set create authorship data if not already set
/*		if (!$address->authorship->createdAt()) {
			$address->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}
*/
		$address->authorship->update(new DateTimeImmutable, $this->_currentUser->id);


		$result = $this->_query->run(
			'INSERT INTO
				user_address
			SET
				line_1     = :line_1?s,
				line_2     = :line_2?sn,
				line_3     = :line_3?sn,
				line_4     = :line_4?sn,
				town       = :town?s,
				state_id   = :state_id?sn,
				country_id = :country_id?s,
				postcode   = :postcode?s,
				telephone  = :telephone?s,
				created_at = :created_at?d,
				created_by = :created_by?in,
				user_id    = :userID?i,
				type 	   = :type?s
			', array(
				'line_1'     => $address->lines[1],
				'line_2'     => $address->lines[2] ?: null,
				'line_3'     => $address->lines[3] ?: null,
				'line_4'     => $address->lines[4] ?: null,
				'town'       => $address->town,
				'state_id'   => $address->stateID ?: null,
				'country_id' => $address->countryID,
				'postcode'   => $address->postcode,
				'telephone'  => $address->telephone,
				'created_at' => $address->authorship->createdAt(),
				'created_by' => $address->authorship->createdBy(),
				'userID'     => $address->userID,
				'type'		 => $address->type,
			)
		);

		if ($lastID = $result->id()) {
			return $this->_loader->getByID($lastID);
		}

		return false;
	}
}