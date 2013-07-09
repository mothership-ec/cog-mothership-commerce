<?php


class OrderBundle extends Item {
	
	static $num = 0;
	
	protected $orderID;
	protected $bundleID;
	protected $name;
	protected $descriptionLocalised;
	protected $price;
	protected $bundleKey;
	protected $basketKey;
	protected $tax;
	protected $taxCode;
	protected $taxRate;
	protected $items = array();
	protected $units = array();
	
	protected $publicProperties = array(
		
		'orderID'   => 0,
		'bundleID'  => 0,
	    'name'      => '', 
		'descriptionLocalised' => '', 
	    'price'     => 0.00,
	    'tax'       => 0.00, 
		'taxCode'   => '',
	    'taxRate'   => 0.00,
	    'bundleKey' => 0
	
	);
	
	
	//INITIALISE BY ADDING A BUNDLE KEY UNIQUE TO THIS ORDER
	public function __construct() {
		$this->bundleKey = self::$num;
		self::$num++;
	}
	
	//RECEIVE A MAP OF THE UNITS AND QUANTITY TO FULFILL THIS BUNDLE
	public function units($units) {
		$this->units = $units; // unitID => quantity
	}
	
	
	//RETURNS A BUNDLE KEY FOR A GIVEN ITEM IF THAT ITEM HAS A PLACE IN THIS BUNDLE
	//ONCE THE KEY HAS BEEN RETURNED, THAT UNIT IS REMOVED FROM THE BUNDLE UNIT MAP
	public function getBundleKey(OrderItem $item) {
		foreach ($this->units as $id => $quantity) {
			if ($quantity > 0 && $id == $item->unitID) {
				$this->units[$id]--;
				return $this->bundleKey;
			}
		}
		return NULL;
	}
	
	
	//CALCULATE THE CORRECT AMOUNT OF TAX FOR THIS ORDER
	public function calculateTax() {
		if (!is_null($this->taxRate)) {
			$this->tax = ($this->price / (100 + $this->taxRate)) * $this->taxRate;
		}
	}


}


?>