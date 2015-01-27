<?php

namespace Message\Mothership\Commerce\Address;

use Message\Mothership\User\Address\Address as BaseAddress;

class Address extends BaseAddress
{
	const DELIVERY = 'delivery';
	const BILLING  = 'billing';
}