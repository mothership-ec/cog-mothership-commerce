<?php


class OrderDespatchPackage extends OrderDespatch {

	protected function setType() {
		$this->typeID = NULL;
	}
	
	public function getTrackingLink() {
		return;
	}
	

}


?>