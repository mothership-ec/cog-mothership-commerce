<?php

namespace Message\Mothership\Commerce\Order;

/**
 * Container classes for order status codes.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Statuses
{
	const CANCELLED            = -300;
	const PAYMENT_PENDING      = -100;
	const AWAITING_DISPATCH    = 0;
	const PROCESSING           = 500;
	const PARTIALLY_DISPATCHED = 800;
	const PARTIALLY_RECEIVED   = 900;
	const DISPATCHED           = 1000;
	const RECEIVED             = 1100;
}