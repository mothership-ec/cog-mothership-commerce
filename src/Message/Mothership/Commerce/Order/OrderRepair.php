<?php


class OrderRepair extends Item {
	
	public $repairID;
	public $orderID;
	public $orderItemID;
	public $catalogueID;
	public $notes;
	public $purchaseDate;
	public $retailer;
	public $faulty;
	protected $options = array();

	
	protected $publicProperties = array(
		'repairID',
		'orderID',
		'orderItemID',
		'catalogueID',
		'notes',
		'retailer',
	);

	public function addOption($option) {
		$this->options[] = $option;
	}

	public function getOptions() {
		return $this->options;
	}
	
}



?>