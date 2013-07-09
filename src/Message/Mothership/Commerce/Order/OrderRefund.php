<?php


class OrderRefund extends Item {

	protected $orderID;
	protected $refundID;
	protected $reasonID;
	protected $reasonName;
	protected $typeID;
	protected $typeName;
	protected $amount;
	protected $reference;
	
	protected $publicProperties = array(
		
		'orderID'    => 0,
	    'refundID'   => 0, 
		'refundName' => '',
	    'typeID'     => 0, 
		'typeName'   => '',
		'reasonID'   => 0, 
		'reasonName' => '',
		'amount'     => 0.00,
	    'reference'  => ''
	
	);
	
	
	public function __construct($amount = NULL, $reasonID = NULL, $typeID = NULL) {
		$this->amount($amount);
		$this->reasonID($reasonID);
		$this->typeID($typeID);
	}
	

}


?>