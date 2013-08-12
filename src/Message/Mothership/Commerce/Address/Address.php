<?php

namespace Message\Mothership\Commerce\Address;

use Message\Cog\ValueObject\Authorship;

class Address
{
	const DELIVERY = 'delivery';
	const BILLING  = 'billing';

	public $id;
	public $type;
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
}