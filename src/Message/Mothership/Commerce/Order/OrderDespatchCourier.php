<?php


class OrderDespatchCourier extends OrderDespatch {

	protected function setType() {
		$this->typeID = 5;
	}
	
	public function getTrackingLink() {
		return;
	}
	

}



?>