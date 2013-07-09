
<?php


class OrderCreateFaux extends OrderCreate {
	
	//SET THE TOTAL FOR THIS ORDER
	public function setTotal($total) {
		$this->total = $total;
	}
	
	//SET THE TOTAL FOR THIS ORDER
	public function setPaid($paid) {
		$this->paid = $paid;
	}
	
}