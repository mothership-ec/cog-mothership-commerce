<?php

namespace Message\Mothership\Commerce\Order\Entity\Address;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;

use Message\Mothership\Commerce\Address\Address as BaseAddress;

/**
 * Represents an address for an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Address extends BaseAddress implements EntityInterface
{
	public $order;

	public $title;
	public $forename;
	public $surname;

	public $authorship;

	public function getName()
	{
		return $this->forename . ($this->surname ? ' ' . $this->surname : '');
	}
}