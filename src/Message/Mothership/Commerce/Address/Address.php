<?php

namespace Message\Mothership\Commerce\Address;

use Message\Cog\ValueObject\Authorship;

class Address
{
	const DELIVERY = 'delivery';
	const BILLING  = 'billing';

	public $id;
	public $userID;
	public $type;
	public $name;
	public $lines = array(
		1 => null,
		2 => null,
		3 => null,
		4 => null,
	);
	public $town;
	public $stateID;
	public $state;
	public $postcode;
	public $country;
	public $countryID;
	public $telephone;
	public $authorship;

	public function __construct()
	{
		$this->authorship = new Authorship;
	}
}