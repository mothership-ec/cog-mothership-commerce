<?php


class OrderCreate extends OrderAppend {


	//CREATE A NEW ORDER OBJECT
	public function __construct($userID, $currencyID, $orderID = null, $date = null) {

		//INITIALISE THE ORDER
		parent::__construct();

		//ADD USER AND CURRENCY;
		$this->userID($userID);
		$this->currencyID($currencyID);
		$this->createOrderID = $orderID;
		$this->createOrderDate = date('Y-m-d H:i:s', $date ?: time());
	}


	//SET THE USER ID
	public function userID($val) {
		$this->userID = (int) $val;
	}


	//SET THE CURRENCY ID
	protected function currencyID($val) {
		$this->currencyID = (string) $val;
	}

	public function shopID($val) {
		$this->shopID = (int) $val;
	}

	public function tillID($val) {
		$this->tillID = (int) $val;
	}

	public function staffID($val) {
		$this->staffID = (int) $val;
	}


	//ADD SHIPPING INFORMATION FOR THIS ORDER
	public function addShipping($shippingID, $shippingName, $shippingAmount) {
		$this->shippingID     = (int) $shippingID;
		$this->shippingName   = (string) $shippingName;
		$this->shippingAmount = (float) $shippingAmount;
		$this->shippingTax 	  = null;
	}

	//ADD A CAMPAIGN TO THIS ORDER
	public function addCampaign(Campaign $campaign) {
		if (!$this->shippingName) {
			throw new OrderException('Shipping must be added before Campaign for free shipping to be calculated');
		}
		//FORCE CAMPAIGN TO RELOAD VALUES FOR ORDER CURRENCY
		$campaign->getValue($this->currencyID);
		if ($campaign->isValid()) {
			$this->campaigns->add($campaign);

			//ADD CAMPAIGN DISCOUNT TO THE ORDER
			$discount = new OrderDiscountCampaign($campaign->getCode());
			$discount->setCampaign($campaign);

			if ($campaign->getPercentage()) {
				$discount->percentage($campaign->getPercentage());
			} else {
				$discount->amount($campaign->getBenefit());
			}

			if ($discount->percentage || $discount->amount) {
				$this->addDiscount($discount);
			}

			//IF CAMPAIGN HAS FREE SHIPPING AND , ADD TO THE ORDER
			if ($campaign->hasFreeDelivery() && $this->shippingAmount == 0) {
				$discount = new OrderDiscountFreeShipping($campaign->getCode());
				$discount->amount($campaign->getShippingBenefit());
				$this->addDiscount($discount);
			}
		}
	}


	//ADD A BASKET OF PRODUCTS TO THIS ORDER
	public function addBasket($basket) {
		try {
			$this->setTaxable();
			parent::addBasket($basket);
			$this->setTotal();
		} catch (Exception $e) {
			$this->fb->addError($e->getMessage());
		}
	}


	//SET THE TOTAL FOR THIS ORDER
	protected function setTotal() {
		$this->total = 0;
		foreach ($this->getBundles() as $bundle) {
			$this->total += $bundle->price;
		}
		foreach ($this->getItems() as $item) {
			if (is_null($item->bundleKey)) {
				$this->total += $item->getPrice();
			}
		}
	}


	//WORK OUT THE DISCOUNT TO APPLY TO THIS ORDER AND THE ITEMS INDIVIDUALLY
	protected function applyDiscount() {
		$this->discount = 0;
		$percentage = 0.0;

		// RESET ITEM LEVEL DISCOUNT AMOUNTS
		foreach($this->getItems() as $item) {
			$item->discount(0);
		}

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

			$discount->setOrderItemDiscounts($this);

		}

		if ($percentage > 0) {
			$this->discount += round(($this->total / 100) * $percentage, 2);
		}
	}


	//SET THE TAX FOR THIS ORDER
	protected function calculateTax() {
		if ($this->taxable) {
			if (is_null(self::$taxRates)) {
				$this->loadTaxRates();
			}
			$this->tax = $this->calculateTaxUsingRates(self::$taxRates);
		}
	}

	//CALCULATE THE TAX FOR THIS ORDER USING A SET OF TAX RATES
	//METHOD MADE PUBLIC AND ABSTRACTED TO ALLOW TAX CALCULATIONS FOR CHANNEL ISLAND DISCOUNT, OFFSET UK VAT
	public function calculateTaxUsingRates(array $taxRates) {

		$orderTax = 0;

		//COLLECT VALUES AND TAX RATES FOR APPLYING DISCOUNTS
		$tax = array();

		//COLLECT ORDER VALUE PER TAX CODE
		//BUNDLES ARE TAXED AT STANDARD RATE
		foreach ($this->getBundles() as $bundle) {
			if (!isset($tax['S'])) {
				$tax['S'] = 0;
			}
			$tax['S'] += $bundle->price;
		}

		//ITEMS ARE TAXED AT INDIVIDUAL RATES
		foreach ($this->getItems() as $item) {
			if (is_null($item->bundleKey)) {
				if (!isset($tax[$item->taxCode])) {
					$tax[$item->taxCode] = 0;
				}
				$tax[$item->taxCode] += round($item->tax, 2);
			}
		}

		$highestTaxRate = 0;

		if($this->total) {
			//CALCULATE TAX PAYABLE ON ORDER
			foreach ($tax as $code => $amount) {
				$orderTax += $amount;

				// USED TO CALCULATE THE TAX RATE PAYABLE ON SHIPPING
				if($taxRates[$code] > $highestTaxRate) {
					$highestTaxRate = $taxRates[$code];
				}
			}
		}


		//ADD SHIPPING AT HIGHEST RATE
		if ($this->shippingAmount && !$this->hasFreeShipping() && $this->taxable) {
			$this->shippingTax = round(($this->shippingAmount / (100 + $highestTaxRate)) * $highestTaxRate, 2);
		}

		//ROUND TAX TO 2 DECIMAL PLACES
		$orderTax = round($orderTax, 2);

		// ORDERS OUTSIDE EU GET A VAT DISCOUNT
		if(!$this->taxable) {
			$taxDiscount = 0;
			foreach ($this->getItems() as $item) {
				$taxDiscount += $item->getTaxDiscount();
			}
			$this->setTaxDiscount($taxDiscount);
		}

		//RETURN ORDERTAX
		return $orderTax;

	}


	//COMMIT A NEW ORDER
	public function commit() {

		if (!$this->orderID) {

			try {
				//VALIDATE THE ORDER
				$this->validate();

				//START A TRANSACTION
				$trans = new DBtransaction;

				//INSERT THE ORDER SUMMARY AND SAVE THE ORDER ID
				$trans->add($this->getInsertQuery());

				// LAST_INSERT_ID DOESNT WORK WHEN THE ORDER ID IS SPECIFIED
				if($this->createOrderID) {
					$trans->add('SET @orderID = '. (int)$this->createOrderID  );
				} else {
					$trans->add('SET @orderID = LAST_INSERT_ID()');
				}

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
					if ($property instanceof Collection && $query = (array) $property->getInsertQuery('@orderID')) {
						foreach ($query as $q) {
							$trans->add($q);
						}
					}
				}

				//INSERT POS DATA IF SET
				if($this->shopID || $this->tillID || $this->staffID) {
					$trans->add('INSERT INTO order_pos SET ' .
								'shop_id = ' . (int) $this->shopID . ', ' .
								'till_id = ' . (int) $this->tillID . ', ' .
								'staff_id = ' . (int) $this->staffID . ', ' .
								'order_id = @orderID');
				}

				//RUN ANY QUERIES FOR DIMENSIONS AS PART OF THIS TRANSACTION
				$dq = (array) DimensionsOrder::getOrderCreationQueries('@orderID');

				foreach ($dq as $q) {
					$trans->add($q);
				}

				//SELECT THE ORDER ID FOR RETURN
				$trans->add('SELECT @orderID AS order_id');

				$trans_copy = clone $trans;

				//RUN THE TRANSACTION
				if ($trans->run()) {

					$this->orderID = $trans->value();

					//SAVE METADATA
					if ($this->metadata instanceof OrderMetadata) {
						$this->metadata->setOrderID($this->orderID);
						$this->metadata->save();
						PersistentBasket::instance()->refresh();
					}

					$this->despatchElectronicVouchers();

				} else {
					throw new OrderException('error saving new order');
				}
			} catch (Exception $e) {
				$this->fb->addError('Order cannot be placed: ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')');
				return false;
			}
		}

		return $this->orderID;
	}

	//CHECK IF AN ORDER IS READY TO BE PAID FOR
	public function complete() {
		try {
			//APPLY DISCOUNT AND SET TAX AMOUNT
			$this->applyDiscount();
			$this->calculateTax();
			$this->validate();
		} catch (Exception $e) {
			$this->fb->addError($e->getMessage());
			return false;
		}
		return true;
	}


	//RETURN QUERY TO INSERT AN ORDER
	protected function getInsertQuery() {
		$DB = new DBquery;
		$query = 'INSERT INTO order_summary SET '
			   . 'order_id = '		  . $DB->null($this->createOrderID).', '
			   . 'order_datetime = '  . $DB->escape($this->createOrderDate).', '
			   . 'order_total = '     . $DB->null($this->total) . ', '
			   . 'order_discount = '  . $DB->null($this->discount) . ', '
			   . 'order_taxable = '   . $DB->null($this->taxable) . ', '
			   . 'order_tax = '       . $DB->null($this->tax) . ', '
			   . 'order_tax_discount = '. $DB->null($this->taxDiscount) . ', '
			   . 'order_payment = '   . $DB->null($this->paid) . ', '
			   . 'order_change = '    . $DB->null($this->change) . ', '
			   . 'user_id = '         . $DB->null($this->userID) . ', '
			   . 'currency_id = '     . $DB->escape($this->getSimpleCurrencyID());
		return $query;

	}


	//DETERMINE IF DELIVERY ADDRESS IS VATABLE
	public function setTaxable() {
		if (!$countryID = $this->getCountryID('delivery')) {
			throw new OrderException('Delivery address not specified');
		}
		$DB = new DBquery;
		$query = "SELECT IF(vat = 'Y', 1, 0) AS taxable "
			   . 'FROM lkp_country_region '
			   . 'WHERE country_id = ' . $DB->escape($countryID);
		if ($DB->query($query)) {
			$this->taxable = $DB->value();
		} else {
			throw new OrderException('Error determining taxable status');
		}
	}


	//GROUP ALL ELECTRONIC GIFT VOUCHERS AND ADD THEM TO AN ELECTRONIC DESPATCH
	protected function despatchElectronicVouchers() {

		$run = false;

		$order = new OrderUpdate($this->orderID);
		$package = new OrderDespatchElectronic;

		foreach($order->getItems() as $item) {

			if($item instanceof OrderItemGiftVoucherElectronic) {
				$package->addItem($item);
				$run = true;
			}

		}

		if($run) {
			if($despatch = $order->addDespatch($package)) {
				if($order->postageDespatch($despatch->despatchID, 'ELECTRONIC', 0, 0)
				&& $order->shipDespatch($despatch->despatchID, 0)) {
					$despatch->send();
				}
			}
		}

	}



}