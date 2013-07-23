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

	public $id;
	public $amount;
	public $percentage;
	public $threshold;
	public $name;
	public $description;

	public function __construct()
	{
		$this->authorship = new Authorship;
	}
}