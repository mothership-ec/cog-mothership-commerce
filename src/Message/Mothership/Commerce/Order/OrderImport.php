<?php


class OrderImport extends OrderCreate {
	
	
	protected $datetime;
	
	
	//CREATE A NEW ORDER OBJECT
	public function __construct($userID, $currencyID, $orderID) {
		
		//INITIALISE THE ORDER
		parent::__construct($userID, $currencyID);
		$this->orderID = (int) $orderID;
	}
	
	
	//SET THE TOTAL FOR THIS ORDER
	public function setTotal($val = NULL) {
		if ($val) {
			$this->total = (float) $val - $this->shippingAmount;
		}
	}
	
	
	public function setDatetime($val) {
		$this->datetime = $val;
	}
	

	//WORK OUT THE DISCOUNT TO APPLY TO THIS ORDER
	protected function applyDiscount() {
		$this->discount = 0;
		$percentage = 0.0;
		foreach ($this->getDiscounts() as $discount) {
			//FREE SHIPPING IS HANDLED SEPARATELY
			if ($discount instanceof OrderDiscountFreeShipping) {
				continue;
			
			//ADD THE FIXED AMOUNTS TOGETHER
			} elseif ($discount->amount) {
				$this->discount += $discount->amount;
			
			//ONLY THE LARGEST PERCENTAGE APPLIES
			} elseif ((float) $discount->percentage > $percentage) {
				$percentage = $discount->percentage;
			}
		}
		//IMPORTED ORDER TOTALS ARE INCLUSIVE OF DISCOUNT SO WE NEED TO ADD THIS BACK IN
		if ($percentage > 0) {			
			$this->discount += ($this->total / (100 - $percentage)) * $percentage;
		}
		$this->total += $this->discount;
	}
	
	
	//SET THE TAX FOR THIS ORDER
	protected function calculateTax() {
		if ($this->taxable) {
			$this->tax = 0;
			
			//CALCULATE TAX PAYABLE ON ORDER
			$this->tax = ($this->total / (100 + $this->getTaxRate('S'))) * $this->getTaxRate('S');
			
			//ADD SHIPPING AT STANDARD RATE
			if ($this->shippingAmount && !$this->hasFreeShipping()) {
				$this->tax += ($this->shippingAmount / (100 + $this->getTaxRate('S'))) * $this->getTaxRate('S');
			}
		}
		//ROUND TAX TO 2 DECIMAL PLACES
		$this->tax = round($this->tax, 2);
	}
	
	
	
	//COMMIT A NEW ORDER
	public function commit() {
		if (!$this->orderID) {
			throw new Exception('OrderImport missing orderID');
		}	
		try {
			//VALIDATE THE ORDER
			$this->validate();
			
			//START A TRANSACTION
			$trans = new DBtransaction;
			
			//INSERT THE ORDER SUMMARY AND SAVE THE ORDER ID
			$trans->add($this->getInsertQuery());
			$trans->add('SET @orderID = ' . $this->orderID);
			
			//INSERT SHIPPING DATA IF SET
			if($this->shippingID || $this->shippingName || $this->shippingAmount || $this->shippingTax) {
				$trans->add('
					INSERT INTO
						order_shipping
					SET
						order_id        = @orderID,
						shipping_id     = ' . $trans->null($this->shippingID) . ',
						shipping_name   = ' . $trans->escape($this->shippingName) . ',
						shipping_amount = ' . $trans->null($this->shippingAmount) . ',
						shipping_tax    = ' . $trans->null($this->shippingTax)
				);
			}

			//INSERT EACH OF THE COLLECTIONS
			foreach ($this as $property) {
				if ($property instanceof Collection && $query = $property->getInsertQuery('@orderID')) {
					$trans->add($query);
				}
			}
			
			//ADD THE ORDER ID TO THE LOG
			$trans->add('INSERT INTO order_import (order_id, order_import_timestamp) VALUES (@orderID, NOW())');
			
			//SELECT THE ORDER ID FOR RETURN
			$trans->add('SELECT @orderID AS order_id');
			
			//RUN THE TRANSACTION
			if ($trans->run()) {
				$this->orderID = $trans->value();
			} else {
				throw new OrderException('error saving new order');
			}
		} catch (Exception $e) {
			$this->fb->addError('Order cannot be placed: ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')');
			return false;
		}
		
		return $this->orderID;
	}
	
	
	//RETURN QUERY TO INSERT AN ORDER 
	protected function getInsertQuery() {
		$DB = new DBquery;
		$query = 'INSERT INTO order_summary SET '
			   . 'order_id = '        . $this->orderID . ', '
			   . 'order_datetime = '  . $DB->escape($this->datetime) . ', '
			   . 'order_total = '     . $DB->null($this->total) . ', '
			   . 'order_discount = '  . $DB->null($this->discount) . ', '
			   . 'order_taxable = '   . $DB->null($this->taxable) . ', '
			   . 'order_tax = '       . $DB->null($this->tax) . ', '
			   . 'order_payment = '   . $DB->null($this->paid) . ', '
			   . 'user_id = '         . $DB->null($this->userID) . ', '
			   . 'currency_id = '     . $DB->escape($this->currencyID);
		return $query;
			   
	}
	
	
} 



?>