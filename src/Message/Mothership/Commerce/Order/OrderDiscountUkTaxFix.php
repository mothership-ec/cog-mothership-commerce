<?php


class OrderDiscountUkTaxFix extends OrderDiscount {
	
	protected function setType() {
		$this->typeID = 4;
	}

}



?>