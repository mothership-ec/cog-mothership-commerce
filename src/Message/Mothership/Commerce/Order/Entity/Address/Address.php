<?php

namespace Message\Mothership\Commerce\Order\Entity\Address;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;

/**
 * Represents an address for an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Address implements EntityInterface
{
	const DELIVERY = 'delivery';
	const BILLING  = 'billing';

	public $order;

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
}