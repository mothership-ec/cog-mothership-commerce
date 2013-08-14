<?php

namespace Message\Mothership\Commerce\User\Address;

use Message\Cog\ValueObject\Authorship;
use Message\Mothership\Commerce\Address\Address as BaseAddress;

class Address extends BaseAddress
{
	public $userID;
	public $authorship;

	public function __construct()
	{
		$this->authorship = new Authorship;
	}
}
