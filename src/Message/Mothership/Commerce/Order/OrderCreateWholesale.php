<?php


class OrderCreateWholesale extends OrderCreate
{
	
	public function __construct($userID, $currencyID, $taxable) {
		parent::__construct($userID, $currencyID);
		$this->taxable = $taxable;
	}
	
	
	public function setTax($amount) {
		$this->tax = $amount;
	}
	
	
	public function setUnitDiscount($unitID, $discount) {
		$this->_unitDiscount[$unitID] = round($discount, 3);
	}
	
	
	protected function setTaxable() {
		return NULL;
	}
	
	
	protected function applyDiscount() {
		$this->discount = 0;
		foreach ($this->_unitDiscount as $amount) {
			$this->discount += (float) $amount;
		}
		if ($this->taxable) {
			$this->discount += ($this->discount * ($this->getTaxRate('S') / 100));
		}
		$this->getMetadata()->set('unitDiscountArray', serialize($this->_unitDiscount));
	}
	
	//COMMIT A NEW ORDER
	public function commit() {
		$return = false;
		parent::commit();
		if ($this->orderID) {
			//SEND ORDER TO DIMENSIONS
			$d = new DimensionsOrderHandler($this);
			$return = $d->sendOrder();
			unset($d);
			//MARK ORDER AS POST PROCESSED SO IT IS IGNORED BY THE CRON JOB
			$db = new DBquery('INSERT IGNORE INTO order_cron (order_id, cron_datetime) VALUES (' . $this->orderID . ', NOW())');
			unset($db);
		}
		return $return;
	}
	
}




?>