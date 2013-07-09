<?php

class OrderPaymentPaypal extends OrderPayment
{

	protected function setType()
	{
		$this->typeID = 15;
	}

}