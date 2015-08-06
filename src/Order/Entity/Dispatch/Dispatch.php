<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;
use Message\Mothership\Commerce\Order\Entity\Item;

use Message\Cog\ValueObject\Authorship;

class Dispatch implements EntityInterface
{
	/**
	 * This prefix should be used for manual dispatches where no tracking code is given,
	 * code must still be set as this is checked against when determining if the 
	 * dispatch has been dispatched
	 */
	const NO_DELIVERY_CODE_PREFIX = 'none#';

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
		$this->items = new Item\Collection;
	}

	/**
	 * Gets the tracking code if it's not set to a none value
	 * 
	 * @return string|null string if non-none like code set.
	 */
	public function getCode()
	{
		if (strpos($this->code, self::NO_DELIVERY_CODE_PREFIX) === false) {
			return $this->code;
		}

		return null;
	}
}