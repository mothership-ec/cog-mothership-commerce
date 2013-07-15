<?php

namespace Message\Mothership\Commerce\Order;

class Create
{
	public function create(Order $order, array $metadata = array())
	{
		// shipping?
	}

	public function addAddress(Entity\Address\Address $address)
	{

	}

	public function addItem(Entity\Item\Item $item)
	{

	}

	public function addPayment(Entity\Payment\Payment $payment)
	{

	}

	public function addDiscount(Entity\Discount\Discount $discount)
	{

	}

	public function addNote(Entity\Note\Note $note)
	{

	}

	public function commit()
	{
		// run transaction
	}
}