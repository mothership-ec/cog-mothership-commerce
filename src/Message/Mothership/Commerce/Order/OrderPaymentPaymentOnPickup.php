<?php

class OrderPaymentPaymentOnPickup extends OrderPayment
{

	protected function setType()
	{
		$this->typeID = 17;
	}

}