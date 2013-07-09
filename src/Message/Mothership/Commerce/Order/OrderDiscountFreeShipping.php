<?php


class OrderDiscountFreeShipping extends OrderDiscount {

	protected function setType() {
		$this->typeID = 3;
	}

}



?>