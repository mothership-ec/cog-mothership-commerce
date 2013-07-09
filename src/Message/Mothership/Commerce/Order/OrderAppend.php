<?php


/*

This abstract class adds the methods that are common to OrderCreate and OrderUpdate,
thereby removing them from the base Order class and casual viewing of the order.


version 2: new Basket object supported.

*/


abstract class OrderAppend extends Order {
	
	
	static protected $taxRates;
	
	
	//ADD AN ADDRESS OBJECT TO THIS ORDER
	public function addAddress(OrderAddress $address) {
		$this->addresses->add($address);
	}
	
	
	//ADD A BASKET OF PRODUCTS TO THIS ORDER
	public function addBasket($basket) {
		
		if(!in_array(get_class($basket), array('Basket', 'TillTransaction'))) {
			return false;
		}

		$bundleKey = 0;
		$units = array();
		
		foreach ($basket->getItems() as $key => $basketItem) {
			
			//ADD BUNDLES
			if ($basketItem instanceof Bundle) {
				for ($i = 0; $i < $basket->getQuantity($key); $i++) {
					
					//CREATE A MAP OF UNITS AND QUANTITIES FOR THIS BUNDLE
					$unitMap = array();
					foreach ($basketItem->getUnits() as $unit) {
						if (!isset($unitMap[$unit->unitID])) {
							$unitMap[$unit->unitID] = 0;
						}
						$unitMap[$unit->unitID]++;
					}
					
					//CREATE A NEW BUNDLE ITEM
					$item = new OrderBundle;
					$item->bundleID($basketItem->bundleID);
					$item->name($basketItem->bundleName);
					$item->descriptionLocalised($basketItem->displayName);
					$item->price($basketItem->getPrice($this->getComplexCurrencyID()));
					$item->units($unitMap);
					$item->basketKey($bundleKey);
					$item->taxCode($basketItem->taxCode);
					
					//ADD ITEM TAX
					$item->taxRate($this->getTaxRate($item->taxCode));
					$item->calculateTax();
					
					//ADD TO BUNDLE COLLECTION
					$this->bundles->add($item);
					
					//REGISTER BUNDLE WITH ITEM COLLECTION
					$this->items->registerBundle($item);
					//COLLECT BUNDLE UNITS
					foreach ($basketItem->getUnits() as $unit) {
						$unit->bundleKey($bundleKey);
						$units[] = $unit;
					}
					
					//INCREMENT THE BUNDLE KEY
					$bundleKey++;
				}
			
			//COLLECT INDIVIDUAL UNITS
			} elseif ($basketItem instanceof Unit) {
				for ($i = 0; $i < $basket->getQuantity($key); $i++) {
					$units[] = $basketItem;
				}
			}
		}
		
		//ADD COLLECTED UNITS TO THE ITEM COLLECTION
		foreach ($units as $unit) {
			$this->addItem($unit);
		}
		
	}
	

	public function addItem(Unit $unit) {
		
		//CREATE A NEW ORDER ITEM
		if($unit->catalogueID && $unit instanceof GiftVoucher) {
			$item = (Config::get('gifting')->isElectronic($unit)) ? new OrderItemGiftVoucherElectronic : new OrderItemGiftVoucher;
			$item->addVoucher($unit);
		}
		else {
			$item = new OrderItem;
		}
		
		$item->taxable($this->taxable);
		$item->addData($unit->getProperties());
		
		//ADD ITEM PRICE
		$item->price($unit->getPrice($this->getComplexCurrencyID()));
		$item->unitCost($unit->getPrice($this->getComplexCurrencyID(), 'cost'));
		$item->rrp($unit->getPrice($this->getComplexCurrencyID(), 'rrp'));
		
		//ADD ITEM TAX
		$item->taxRate($this->getTaxRate($item->taxCode, 'GB'));
		$item->calculateTax();
		
		//ADD TO COLLECTION
		$this->items->add($item);
		
	}

	
	//ADD A DISCOUNT OBJECT TO THIS ORDER
	public function addDiscount(OrderDiscount $discount) {
		$this->discounts->add($discount);
		$this->applyDiscount();
	}
	
	
	//ADD A PAYMENT TO THIS ORDER
	public function addPayment(OrderPayment $payment) {
		$this->payments->add($payment);
		$this->updatePaid();
	}
	
	
	//UPDATE THE ORDER SUMMARY WITH PAID AMOUNT
	protected function updatePaid() {
		$this->paid = 0;
		foreach ($this->getPayments() as $payment) {
			$this->paid += $payment->amount;
		}
		$this->change = $this->paid - $this->getTotal();
		$this->change = ($this->change < 0) ? 0 : $this->change;
	}
	

	public function getPaymentObject($typeID, $amount) {
		$DB = new DBquery("SELECT payment_type_name FROM order_payment_type WHERE payment_type_id = ".(int) $typeID);
		if($paymentName = $DB->value()) {
			$class = 'OrderPayment'.str_replace(' ', '', ucwords($paymentName));
			return new $class($amount);
		}
		return true;
	}

	
	//GET THE CURRENT TAX RATE FOR A TAX CODE
	protected function getTaxRate($code, $override) {
		if($this->taxable || $override) {
			if (is_null(self::$taxRates)) {
				$this->loadTaxRates($override);
			}
			if (isset(self::$taxRates[$code])) {
				return self::$taxRates[$code];
			} else {
				throw new OrderException("VAT code '" . $code . "' not recognised");
			}
		}

		return NULL;
	}
	
	
	
	//GET AN ARRAY OF TAX RATES
	protected function loadTaxRates($override = false) {
		$orderTax = new OrderTax;
		$countryID = $override ?: $this->getCountryID('delivery');

		foreach ($orderTax->getRates($countryID) as $code => $rate) {
			self::$taxRates[$code] = $rate;
		}

		if (count(self::$taxRates) < 1) {
			throw new OrderException('Error loading VAT rates');
		}
	}
	

	// AG - Added this because validate() is missing on call to amountDue in OrderUpdate class	
	//MAKE SURE AN ORDER IS COMPLETE BEFORE COMMITTING
	public function validate() {
		//REQUIRED PROPERTIES
		$required = array(
//			'userID',
			'currencyID',
//			'total'
		);
		if(get_class($this) != 'OrderCreateTillTransaction' && !$this->shopID) {
			$required[] = 'shippingID';
			$required[] = 'shippingName';
			$required[] = 'shippingAmount';
		}
		foreach ($required as $property) {
			if (is_null($this->{$property})) {
				throw new OrderException($property . ' is required');
			}
		}
		//DELIVERY ADDRESS
		if (!$this->getAddress('delivery') && get_class($this) != 'OrderCreateTillTransaction' && !$this->shopID) {
			throw new OrderException('A delivery address is required');
		}	
		//AT LEAST ONE ITEM
		if (count($this->getItems()) < 1) {
			throw new OrderException('There are no items in this order');
		}
		return true;
	}
	
	
	//GET THE UNPAID BALANCE ON THE ORDER
	public function amountDue($onlyAcceptedReturns=true, $validate = true) {
		if($validate) {
			$this->validate();
		}
		return parent::amountDue($onlyAcceptedReturns);
	}
	
	public function setTaxDiscount($value) {
		$this->taxDiscount = $value;
	}

	//ADD NOTE TO THIS ORDER
	public function addNote(OrderNote $note) {
		$noteText = trim($note->note);
		if(empty($noteText)) {
			return false;
		}
		$this->notes->add($note);
		$note->sendCustomerNotification($this);
	}
		
}