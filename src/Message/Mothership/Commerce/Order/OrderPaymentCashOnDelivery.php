<?php

class OrderPaymentCashOnDelivery extends OrderPayment
{

	protected function setType()
	{
		$this->typeID = 16;
	}

}