<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;

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

	public $items = array();

	public function __construct()
	{
		$this->authorship = new Authorship;
	}






	public function addItem(OrderItem $item)
	{
		$this->orderItems[$item->itemID] = $item;
		$this->itemIDs[] = $item->itemID;
		$this->addWeight($item->weight);
	}

	public function getItemIDs() {
		return $this->itemIDs;
	}

	public function getItems() {
		return $this->orderItems;
	}


	public function getCustomsValue() {
		$order = new Order($this->orderID);
		$value = 0;
		foreach ($this->getItemIDs() as $itemID) {
			$item = $order->getItems($itemID);
			$value += $item->price;
		}
		return $value - (($value / $order->getsubTotal()) * $order->getDiscount());
	}
}