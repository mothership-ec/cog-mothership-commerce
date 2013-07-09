<?php


abstract class OrderPayment extends Item {

	protected $orderID;
	protected $paymentID;
	protected $typeID;
	protected $typeName;
	protected $amount;
	protected $reference;
	protected $datetime;
	protected $timestamp;
	protected $note;
	
	protected $publicProperties = array(
		
		'orderID'    => 0,
	    'paymentID'  => 0, 
	    'typeID'     => 0,
		'typeName'   => '',
		'amount'     => 0.00,
	    'reference'  => '',
	    'datetime'   => '',
	    'timestamp'  => 0,
		'note'       => ''
	
	);
	
	
	public function __construct($amount = NULL, $reference = NULL) {
		$this->setType();
		$this->reference($reference);
		$this->amount($amount);
	}
	
	
	abstract protected function setType();
	
	
	public function type() {
		return $this->typeID;
	}

}


?>