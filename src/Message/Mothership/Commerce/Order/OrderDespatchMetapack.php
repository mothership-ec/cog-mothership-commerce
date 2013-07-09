<?php


class OrderDespatchMetapack extends OrderDespatch {

	protected $_tracking;

	protected function setType() {
		$this->typeID = 10;
	}
	
	public function getTrackingLink() {
		return;
	}

	public function getTrackingObject() {
		if(!$this->_tracking) {
			$this->_tracking = \MetaPack\MetaPack::instance()->getTrackingDetails($this);
		}
		return $this->_tracking;
	}

	public function getTrackingStatusText() {
		return ($this->getTrackingObject()) ? $this->getTrackingObject()->getStatusText() : 'Tracking information unavailable';
	}
	

}



?>