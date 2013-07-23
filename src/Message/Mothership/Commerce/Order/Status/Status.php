<?php

namespace Message\Mothership\Commerce\Order\Status;

use Message\Cog\ValueObject\Authorship;

/**
 * Order status model.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Status
{
	// VOID
	// CANCELLED
	// PENDING
	// ORDERED / READY?
	// PRINTED
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


	// allowed on order:
	// VOID
	// CANCELLED
	// PENDING
	// ORDERED / READY
	// "PROCESSING"
	// SHIPPED
	// PART SHIPPED
	// RECEIVED
	//
	//
	// "namespaces":
	// - hold (< 0) - cannot be released to dispatch
	// - 0 - ready = release to dispatch
	// - 0 > 1000 = "in despatch"
	// - 1000+ = "post-despatch statuses" (returns, "received" if tracking API permits, repairs?)
	// - 2000 = fully returned

	public $code;
	public $name;

	public function __construct($code, $name)
	{
		$this->code = (int) $code;
		$this->name = $name;
	}

	public function __toString()
	{
		return sprintf('(%i) %s', $this->code, $this->name);
	}
}