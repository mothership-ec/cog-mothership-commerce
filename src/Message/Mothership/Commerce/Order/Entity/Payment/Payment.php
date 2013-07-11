<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\Cog\ValueObject\Authorship;

/**
 * Represents a payment on an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Payment
{
	public $id;
	public $authorship;
	public $order;
	public $return;
	public $method;
	public $amount;
	public $reference;

	public function __construct()
	{
		$this->authorship = new Authorship;
	}
}