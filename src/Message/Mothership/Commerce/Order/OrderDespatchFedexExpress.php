<?php


class OrderDespatchFedexExpress extends OrderDespatchFedex {

	protected function setType() {
		$this->typeID = 4;
	}
	

}



?>