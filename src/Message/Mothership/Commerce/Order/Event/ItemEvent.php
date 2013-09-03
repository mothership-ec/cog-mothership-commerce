<?php

namespace Message\Mothership\Commerce\Order\Event;

use Message\Mothership\Commerce\Order\Order;
use Message\Mothership\Commerce\Order\Entity\Item\Item;

/**
 * 
 */
class ItemEvent extends Event
{
	protected $_item;

	public function __construct(Order $order, Item $item)
	{
		parent::__construct($order);
		$this->setItem($item);
	}

	public function getItem()
	{
		return $this->_item;
	}

	public function setItem(Item $item)
	{
		$this->_item = $item;
	}
}