<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;
use Message\Mothership\Commerce\Order\Entity\Item;

use Message\Cog\ValueObject\Authorship;

class Dispatch implements EntityInterface
{
	public $id;

	public $order;
	public $authorship;
	public $shippedAt;
	public $shippedBy;

	public $method;
	public $code;
	public $cost;
	public $weight;

	public $items;

	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship
			->disableUpdate();

		$this->items = new Item\Collection;
	}
}