<?php

abstract class OrderDiscount extends Item
{

	protected $orderID;
	protected $discountID;
	protected $typeID;
	protected $typeName;
	protected $amount;
	protected $percentage;
	
	protected $publicProperties = array(
		'typeID'     => 0,
		'typeName'   => '',
		'discountID' => '', 
		'amount'     => 0.00,
		'percentage' => 0.00,
	);
	
	
	public function __construct($discountID = null, $amount = null, $percentage = null)
	{
		$this->setType();
		$this->discountID($discountID);
		$this->amount($amount);
		$this->percentage($percentage);
	}
	
	public function type()
	{
		return $this->typeID;
	}

	/**
	 * Set discount at item level. If it's a percentage
	 * discount, that's easy. But if it's a fixed-aount
	 * discount, then we proportionally apportion the 
	 * fixed discount amount to the items in the order 
	 * based on their retail price.
	 *
	 * This solution avoids rounding errors as the
	 * rounding error is added up over time as the
	 * item discount is calculated.
	 *
	 * @link http://stackoverflow.com/a/1925719
	 **/
	public function setOrderItemDiscounts(Order &$order)
	{
		// PERCENTAGE DISCOUNT
		if ($this->percentage) {
			foreach ($order->getItems() as $item) {
				$item->discount((($item->originalPrice / 100) * (float) $this->percentage));
			}
		}
		// FIXED AMOUNT DISCOUNT
		else {
			$totalDiscount = (float) $this->amount;
			$r = 0;
			foreach ($order->getItems() as $item) {
				$oldR = $r;
				$r += ($item->originalPrice / $order->total) * $totalDiscount;
				$item->discount(round($r, 2) - round($oldR, 2));
			}
		}

		return $order;
	}

	abstract protected function setType();

}