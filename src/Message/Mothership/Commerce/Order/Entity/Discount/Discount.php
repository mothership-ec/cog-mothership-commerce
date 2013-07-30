<?php

namespace Message\Mothership\Commerce\Order\Entity\Discount;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;

use Message\Cog\ValueObject\Authorship;

/**
 * Represents a discount for an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Discount implements EntityInterface
{
	public $authorship;

	public $order;

	public $discount;

	public $items = array();

	public $id;
	public $code;
	public $amount;
	public $percentage;
	public $name;
	public $description;

	public function __construct()
	{
		$this->authorship = new Authorship;
	}
}