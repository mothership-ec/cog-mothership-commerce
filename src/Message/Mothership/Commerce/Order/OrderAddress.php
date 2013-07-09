<?php


abstract class OrderAddress extends Item {

	protected $typeID;
	protected $typeName;
	protected $orderID;
	protected $name;
	protected $address;
	protected $address_1;
	protected $address_2;
	protected $town;
	protected $stateID;
	protected $state;
	protected $postcode;
	protected $country;
	protected $countryID;
	protected $telephone;
	
	protected $publicProperties = array(
		
		'orderID'   => 0,
		'typeName'  => '',
	    'name'      => '', 
	    'address'   => '',
	    'address_1'   => '',
	    'address_2'   => '',
		'town'      => '',
	    'state'     => '',
	    'stateID'   => '',
		'postcode'  => '',
	    'country'   => '',
	    'countryID' => '',
	    'telephone' => ''
	
	);
	
	public function __construct() {
		$this->setType();
	}
	
	abstract protected function setType();
	
	public function type() {
		return $this->typeID;
	}
	
	
	//RETURN ADDRESS AS AN ARRAY OF LINES TO PRINT
	public function label($exclude = array()) {
		$bits = array(
			'name',
/* 			'address', */
			'address_1',
			'address_2',
			'town',
			'state',
			'postcode',
			'country'
		);

		foreach($exclude as $value) {
			if(($key = array_search($value, $bits)) !== false) {
				unset($bits[$key]);
			}
		}

		$label = array();
		foreach ($bits as $property) {
			if (!empty($this->{$property})) {
				foreach (preg_split('/[\n\r]+/', $this->{$property}) as $line) {
					$label[] = trim($line);
				}
			}
		}
		return $label;
	}
	
	
	public function update($data) {
		foreach ($data as $key => $val) {
			if (isset($this->publicProperties[$key])) {
				$this->{$key}($val);
			}
		}
		$DB = new DBquery;
		$query = 'UPDATE order_address SET '
			   . 'address_address   = ' . $DB->escape($this->address_1) ."\r\n" .$DB->escape($this->address_2). ', '
			   . 'address_address_1   = ' . $DB->escape($this->address_1) . ', '
			   . 'address_address_2   = ' . $DB->escape($this->address_2) . ', '
			   . 'address_town      = ' . $DB->escape($this->town) . ', '
			   . 'address_state_id  = ' . $DB->escape($this->stateID) . ', '
			   . 'address_state     = ' . $DB->escape($this->state) . ', '
			   . 'address_postcode  = ' . $DB->escape($this->postcode) . ', '
			   . 'address_telephone = ' . $DB->escape($this->telephone) . ' '
			   . 'WHERE order_id    = ' . $this->orderID . ' '
			   . 'AND address_type_id = ' . $this->typeID;
		$DB->query($query);
		return $DB->result();
	}

}