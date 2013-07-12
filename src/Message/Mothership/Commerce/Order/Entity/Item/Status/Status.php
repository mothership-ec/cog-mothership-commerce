<?php

namespace Message\Mothership\Commerce\Order\Entity\Item\Status;

use Message\Cog\ValueObject\Authorship;

class Status
{
	const ORDERED = 100;

	// VOID
	// CANCELLED
	// PENDING
	// ORDERED
	// PRINTED / READY?
	// PACKED
	// POSTAGED
	// PART SHIPPED (?) - think this can go now
	// SHIPPED
	// RETURNED
	// REFUNDED
	// EXCHANGED
	// ON HOLD
	// RECEIVED

	// TO BE RETURNED (RETURN BOOKED?)
	// RETURN RECEIVED
	// PAYMENT DUE (ON HOLD)
	// PAID ??
	// COMPLETE (GO BACK TO READY)?


	public $item;
	public $authorship;

	public $status;

	public function __construct()
	{
		$this->authorship = new Authorship;
	}
}