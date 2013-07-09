<?php


class OrderDespatchCollect extends OrderDespatch {

	protected function setType() {
		$this->typeID = 9;
	}
	
	public function getTrackingLink() {
		return;
	}
	

}



?>