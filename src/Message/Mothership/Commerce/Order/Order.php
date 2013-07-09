<?php

class Order
{

	const RETURN_STATUS_PAID = 54;

	protected $fb;

	protected $orderID;
	protected $userID;
	protected $userName;
	protected $placedTimestamp;
	protected $updateTimestamp;
	protected $taxable;

	protected $statusID;
	protected $statusName;

	protected $total;
	protected $tax;
	protected $taxDiscount;
	protected $discount;
	protected $paid;
	protected $change;
	protected $currencyID;
	protected $currencySymbol;
	protected $shippingID;
	protected $shippingName;
	protected $shippingAmount;
	protected $shippingTax;

	protected $shopID;
	protected $shopName;
	protected $tillID;
	protected $staffID;

	protected $addresses;
	protected $items;
	protected $bundles;
	protected $campaigns;
	protected $discounts;
	protected $payments;
	protected $despatches;
	protected $refunds;
	protected $returns;
	protected $receipts;
	protected $repairs;
	protected $notes;

	protected $metadata;

	protected $localeData;

	protected $_unitDiscount = array(); //only used by wholesale orders

	protected $publicProperties = array(

		'orderID',
		'userID',
		'userName',
		'placedTimestamp',
		'updateTimestamp',
		'taxable',
		'total',
		'change',
		'paid',
		'discount',
		'tax',
		'currencyID',
		'currencySymbol',
		'shippingID',
		'shippingName',
		'shippingAmount',
		'shippingTax',
		'shopID',
		'shopName',
		'tillID',
		'staffID',
		'statusName',
		'statusID',
		'taxDiscount',
	);


	//CREATE A NEW ORDER OBJECT
	public function __construct($orderID = NULL) {

		//INITIALISE
		$this->init();

		//SAVE THE ORDER ID
		$this->orderID    = $orderID ? (int) $orderID : NULL;

		//SET THE COLLECTIONS
		$this->items          = new OrderItemCollection($this->orderID);
		$this->bundles        = new OrderBundleCollection($this->orderID);
		$this->addresses      = new OrderAddressCollection($this->orderID);
		$this->campaigns      = new OrderCampaignCollection($this->orderID);
		$this->discounts      = new OrderDiscountCollection($this->orderID);
		$this->payments       = new OrderPaymentCollection($this->orderID);
		$this->despatches     = new OrderDespatchCollection($this->orderID);
		$this->returns        = new OrderReturnCollection($this->orderID);
		$this->refunds        = new OrderRefundCollection($this->orderID);
		$this->receipts       = new OrderReceiptCollection($this->orderID);
		$this->repairs        = new OrderRepairCollection($this->orderID);
		$this->notes          = new OrderNoteCollection($this->orderID);

		//LOAD THE ORDER IF WE HAVE AN ID
		if ($this->orderID) {
			try {
				$this->load();
			} catch (Exception $e) {
				$this->orderID = NULL;
				$this->fb->addError($e->getMessage());
			}
		}
	}


	//METHOD TO INTIALISE ORDER AFTER RETRIEVAL FROM TEMP, CREATION, ETC
	public function init() {
		//GRAB AN INSTANCE OF FEEDBACK
		$this->fb = Feedback::getInstance();
	}


	//FETCH THE ORDER DETAILS FROM THE DATABASE
	protected function load() {
		$query = '
			SELECT
				UNIX_TIMESTAMP(order_datetime) AS placedTimestamp,
				UNIX_TIMESTAMP(order_updated)  AS updateTimestamp,
				order_total                    AS total,
				order_discount                 AS discount,
				order_taxable                  AS taxable,
				order_tax                      AS tax,
				order_tax_discount             AS taxDiscount,
				order_payment                  AS paid,
				order_change                   AS `change`,
				order_summary.user_id          AS userID,
				IF(
					user_forename IS NOT NULL,
					CONCAT_WS(" ", user_forename, user_surname),
					"unknown"
				)                              AS userName,
				currency_id                    AS currencyID,
				currency_name                  AS currencySymbol,
				shipping_id                    AS shippingID,
				shipping_name                  AS shippingName,
				shipping_amount                AS shippingAmount,
				shipping_tax                   AS shippingTax,
				shop_id                        AS shopID,
				shop.name                      AS shopName,
				till_id                        AS tillID,
				staff_id                       AS staffID,
				status_id                      AS statusID,
				status_name                    AS statusName
			FROM
				order_summary
			LEFT JOIN
				order_shipping USING (order_id)
			JOIN
				order_status_name USING (status_id)
			JOIN
				val_currency USING (currency_id)
			LEFT JOIN
				val_user ON order_summary.user_id = val_user.user_id
			LEFT JOIN
				order_pos USING (order_id)
			LEFT JOIN
				shop USING (shop_id)
			WHERE
				order_id = ' . $this->orderID;
		$DB = new DBquery($query);
		if ($row = $DB->row()) {
			foreach ($row as $key => $val) {
				$this->{$key} = $val;
			}
		} else {
			throw new OrderException('Unable to retrieve order #' . $this->orderID);
		}
	}


	//RETURN PROPERTIES THAT ARE PSUEDO PUBLIC
	public function __get($property) {
		switch ($property) {
			case 'orderDate':
				return $this->formatDate($this->placedTimestamp);
			default:
				if (!is_null($this->getMetadata()->{$property})) {
					return $this->getMetadata()->{$property};
				} else if (in_array($property, $this->publicProperties)) {
					return $this->{$property};
				} else {
					throw new OrderException($property . ' is not a property of ' . get_class($this));
				}
		}
	}


	//GET THE ORDER ITEMS AS AN ARRAY WITH BUNDLE ITEMS GROUPED IN THEIR BUNDLES
	public function getItemArray($filter = NULL, $rollUpQuantities = false) {
		$array   = array();
		$items   = array();
		$bundles = array();

		//GROUP THE ORDER ITEMS BY BUNDLE KEY AND UNIT ID
		foreach ($this->getItems($filter) as $item) {
			$items[$item->bundleKey][$item->unitID][] = $item;
		}
		//GROUP THE BUNDLES BY BUNDLE KEY AND BUNDLE ID
		foreach ($this->getBundles() as $bundle) {
			$bundles[$bundle->bundleKey][] = $bundle;
		}

		//GROUP THE ITEMS WITHIN THEIR BUNDLES
		foreach ($items as $key => $orderItems) {
			$rows = array();
			foreach ($orderItems as $orderItem) {
				if ($rollUpQuantities) {
					$line = new OrderLineItem($orderItem[0]);
					$line->quantity(count($orderItem));
					$line->setItems($orderItem);
					$line->shortStatus = $orderItem[0]->shortStatus();
					$rows[] = $line;
				} else {
					foreach ($orderItem as $unit) {
						$line = new OrderLineItem($unit);
						$line->shortStatus = $unit->shortStatus();
						$rows[] = $line;
					}
				}
			}
			//ADD ITEMS TO BUNDLE IF THERE IS A BUNDLE KEY
			if (is_int($key)) {
				$bundle = new OrderLineItem($bundles[$key][0]);
				$bundle->quantity(count($bundles[$key]));
				foreach ($rows as $line) {
					$bundle->addItem($line);
				}
				$array[] = $bundle;
			//OTHERWISE ADD EACH ITEM INDIVIDUALLY
			} else {
				foreach ($rows as $line) {
					$array[] = $line;
				}
			}
		}
		return $array;
	}


	//RETURN THE ORDER ITEMS
	public function getItems($filter = NULL) {
		$this->items->load();
		if ($filter) {
			switch ($filter) {
				case 'SKIP_RETURNS':
					$items = array();
					foreach ($this->getItems() as $item) {
						if ($item->statusID <= ORDER_STATUS_RECEIVED) {
							$items[] = $item;
						}
					}
					return $items;
					break;

				default: //LOOK FOR A SPECIFIC ITEM
					foreach ($this->getItems() as $item) {
						if ($item->itemID == $filter) {
							return $item;
						}
					}
					return false;

			}
		}
		return $this->items->getItems();
	}

	public function loadFromHash($hash) {
		$DB = new DBquery;
		$query = "SELECT order_id FROM lkp_order_basket WHERE hash = ".$DB->escape($hash)."";
		if ($DB->query($query)) {
		    $this->__construct($DB->value());
		    return true;
		}
		return false;
	}



	//RETURN THE BUNDLE ITEMS
	public function getBundles() {
		$this->bundles->load();
		return $this->bundles->getItems();
	}


	//RETURN THE ADDRESS ITEMS
	public function getAddress($type) {
		$this->addresses->load();
		foreach ($this->addresses->getItems() as $address) {
			$class = 'OrderAddress' . ucfirst(strtolower($type));
			if ($address instanceof $class) {
				return $address;
			}
		}
		return false;
	}


	//GET THE COUNTRY ID FOR THIS ORDER
	public function getCountryID($addressType) {
		if ($address = $this->getAddress($addressType)) {
			return $address->countryID;
		}
		elseif($this->shopID) {
			$DB = new DBquery;
			$query = "SELECT country_id FROM shop JOIN lkp_address_country USING (address_id) WHERE shop_id = ".$this->shopID;
			if ($DB->query($query)) {
				return $DB->value();
			}
		}
		return NULL;
	}


	//GET THE LOCALE INFORMATION FOR THIS ORDER (BASED ON DELIVERY COUNTRY)
	public function getLocale() {
		if (!$this->localeData && $address = $this->getAddress('delivery')) {

			$db = new DBquery;

			$sql = 'SELECT locale.*

					FROM locale
					JOIN lkp_region_locale USING (locale_id)
					JOIN lkp_country_region USING (region_id)

					WHERE country_id = ' . $db->escape($address->countryID);

			$db->query($sql);

			if($row = $db->row()) {
				$this->localeData = new stdClass;
				foreach($row as $key => $val) {
					$this->localeData->{toCamelCaps($key)} = $val;
				}
				return $this->localeData;
			}

		}
		return $this->localeData;
	}


	public function getStaffForename() {
		if($this->staffID) {
			$user = getUserDetails($this->staffID);
			return $user['user_forename'];
		}
	}

	public function getStaffName() {
		if($this->staffID) {
			$user = getUserDetails($this->staffID);
			return $user['user_forename'].' '.$user['user_surname'];
		}
	}


	//RETURN THE DISCOUNT ITEMS
	public function getDiscounts() {
		$this->discounts->load();
		return $this->discounts->getItems();
	}


	//GET THE DISCOUNT APPLIED TO A UNIT
	/*
	NOTE: This method only works with wholesale orders.
	It was moved here from the OrderCreateWholesale class to allow access from OrderUpdate

	*/
	public function getUnitDiscount($unitID) {
		return (isset($this->_unitDiscount[$unitID]) ? $this->_unitDiscount[$unitID] : 0);
	}


	//RETURN THE CAMPAIGN ITEMS
	public function getCampaigns() {
		$this->campaigns->load();
		return $this->campaigns->getItems();
	}


	//RETURN THE DESPATCH ITEMS
	public function getDespatches($despatchID = NULL) {
		$this->despatches->load();
		if ($despatchID) {
			foreach ($this->getDespatches() as $despatch) {
				if ($despatch->despatchID == $despatchID) {
					return $despatch;
				}
			}
			return NULL;
		}
		return $this->despatches->getItems();
	}


	//RETURN THE PAYMENT ITEMS
	public function getPayments($id = NULL) {
		$this->payments->load();
		if ($id) {
			foreach ($this->payments->getItems() as $payment) {
				if ($payment->paymentID == $id) {
					return $payment;
				}
			}
			return NULL;
		}
		return $this->payments->getItems();
	}


	//RETURN THE PAYMENT ITEMS
	public function getRefunds($id = NULL) {
		$this->refunds->load();
		if ($id) {
			foreach ($this->refunds->getItems() as $refund) {
				if ($refund->refundID == $id) {
					return $refund;
				}
			}
			return NULL;
		}
		return $this->refunds->getItems();
	}


	//RETURN THE PAYMENT ITEMS
	public function getReturns($id = NULL) {
		$this->returns->load();
		if ($id) {
			foreach ($this->returns->getItems() as $return) {
				if ($return->returnID == $id) {
					return $return;
				}
			}
			return NULL;
		}
		return $this->returns->getItems();
	}


	//RETURN AN ARRAY OF [ONLY] ACTIVE RETURNS
	public function getReturnsByStatus($status) {
		$returns = array();
		switch ($status) {
			case 'INCOMPLETE':
				foreach ($this->getReturns() as $return) {
					if ($return->statusID < RETURN_STATUS_COMPLETE) {
						$returns[] = $return;
					}
				}
				break;
			default:
				$returns[] = $return;
		}
		return $returns;
	}



	//RETURN THE RECEIPTS
	public function getReceipts() {
		$this->receipts->load();
		return $this->receipts->getItems();
	}

	//RETURN THE REPAIRS
	public function getRepairs() {
		$this->repairs->load();
		return $this->repairs->getItems();
	}

	//RETURN AN ARRAY OF
	public function getHistory() {
		$history = array();
		$query = 'SELECT item_id, status_id, status_datetime, staff_id '
			   . 'FROM order_item_status '
			   . 'WHERE order_id = ' . $this->orderID . ' '
			   . 'ORDER BY status_id ASC, status_datetime DESC';
		$DB = new DBquery($query);
		while ($row = $DB->row()) {
			$item = NULL;
			$item->itemID   = $row['item_id'];
			$item->dateTime = $row['status_datetime'];
			$item->staffID  = $row['staff_id'];

			$history[$row['status_id']][] = $item;
		}
		return $history;
	}


	//DOES THE ORDER INCLUDE FREE SHIPPING?
	public function hasFreeShipping() {
		foreach ($this->getDiscounts() as $item) {
			if ($item instanceof OrderDiscountFreeShipping) {
				return true;
			}
		}
		return false;
	}

	public function setIdLookUp($txCode) {
		$DB = new DBquery;
		$hash = md5($this->orderID.":".$txCode);
		$DB->query("INSERT INTO lkp_order_basket SET order_id =".$this->orderID.", basket_id = ".$DB->escape($txCode).", hash=".$DB->escape($hash));
		return $hash;
	}


	//RETURN THE TOTAL DISCOUNT ON THIS ORDER
	//IF A TYPE IS PASSED IN, EITHER RETURN THE DISCOUNT VALUE FOR THIS TYPE
	//OR EXCLUDE THIS TYPE FROM THE TOTAL, DEPENDENT ON $include
	public function getDiscount($type = NULL, $exclude = false) {
		$discountVal  = $this->discount;
		$typeDiscount = 0;
		if ($type) {
			$class = 'OrderDiscount' . $type;
			foreach ($this->getDiscounts() as $discount) {
				if (get_class($discount) == $class) {
					$typeDiscount += $discount->amount;
				}
			}
			if ($exclude) {
				$discountVal -= $typeDiscount;
			} else {
				$discountVal = $typeDiscount;
			}
		}
		return $discountVal;
	}


	public function getSubtotal() {
		return $this->total;
	}


	//GET TOTAL FOR THIS ORDER
	public function getTotal() {
		return ($this->total + $this->shippingAmount) - $this->discount - $this->taxDiscount;
	}


	//GET THE UNPAID BALANCE ON THE ORDER
	public function amountDue($onlyAcceptedReturns=true) {
		//GRAB THE ORDER TOTAL
		$due  = $this->getTotal();
		//DECREMENT PAYMENTS RECEIVED
		$due -= $this->paid;
		//DECREMENT REFUNDS MADE
		//foreach ($this->getRefunds() as $refund) {
		//	$due -= $refund->amount;
		//}

		//LOOK FOR RETURNS WITH BALANCING PAYMENTS
		foreach ($this->getReturns() as $return) {
			if ($return->statusID < self::RETURN_STATUS_PAID &&
				($return->accepted || !$onlyAcceptedReturns)) {
				$due += $return->balancingPayment;
			}
		}
		return $due;
	}


	//GET THE PAID BALANCE ON THE ORDER
	public function amountPaid() {
		return $this->paid;
	}


	//RETURN THE SIMPLE CURRENCYID
	public function getSimpleCurrencyID() {
		$id = explode(':', $this->currencyID);
		end($id);
		return current($id);
	}


	//RETURN A COMPLEX LOCALE_ID:CURRENCY_ID CURRENCY ID
	public function getComplexCurrencyID() {

		$badCurrencyID = array('AU:USD' => 'RW:USD', 'US:GBP' => 'UK:GBP');

		if (!strstr($this->currencyID, ':')) {
			//DETERMINE LOCALE BASED ON DELIVERY ADDRESS
			$localeID = ($this->getAddress('delivery')) ? getLocaleForCountry($this->getAddress('delivery')->countryID) : Locale::DEFAULT_LOCALE_ID;
			$complexCurrencyID = strtoupper($localeID . ':' . $this->currencyID);
			return (isset($badCurrencyID[$complexCurrencyID])? $badCurrencyID[$complexCurrencyID] : $complexCurrencyID);
		}
		return (isset($badCurrencyID[$this->currencyID])? $badCurrencyID[$this->currencyID] : $this->currencyID);
	}


	//RETURN THE METADATA OBJECT FOR ARBITRARY DATA ASSOCIATED WITH THIS ORDER
	public function getMetadata() {
		if (is_null($this->metadata)) {
			$this->metadata = new OrderMetadata($this->orderID);
		}
		return $this->metadata;
	}


	//LOAD ALL COLLECTIONS
	public function loadAll() {
		foreach ($this as $property) {
			if ($property instanceof OrderCollection) {
				$property->load();
			}
		}
	}


	//FORMAT AN ORDER DATE
	protected function formatDate($date) {
		return date('l jS F Y H:i:s', $date);
	}

	public function getLatestReturn() {
		$get = new DBquery("SELECT *, UNIX_TIMESTAMP(return_datetime) as return_datetime FROM order_item_return WHERE order_id = ".$this->orderID." ORDER BY return_datetime DESC LIMIT 1");
		return (object) $get->row();
	}


	//MIMICKS PREVIOUS IMPLEMENTATION OF INVOICE OUTPUT FOR SIMPLICITY
	public function getInvoiceHTML() {
		$html = '<dl>' . "\n";
		$html.= "<dt>Order number</dt>\n";
		$html.= "<dd>".$this->orderID."</dd>\n";
		$html.= "<dt>Order date</dt>\n";
		$html.= "<dd>".$this->orderDate."</dd>\n";
		$html.= "<dt>Customer name</dt>\n";
		$html.= "<dd>".$this->userName."</dd>\n";
		$html.= "<dt>Account Number</dt>\n";
		$html.= "<dd>".$this->userID."</dd>\n";
		foreach ($this->campaigns as $campaign) {
			$html.= "<dt>Campaign name</dt>\n";
			$html.= "<dd>".$campaign->name . ' (' . $campaign->code . ")</dd>\n";
		}
		$html.= "<dt>Delivery Method</dt>\n";
		$html.= "<dd>".$this->shippingName."</dd>\n";
		$html.= "</dl>\n";

		$html.= "<table>\n";
		$html.= "<thead>\n";
		$html.= "<tr>\n";
		$html.= "<th>Description</th>\n";

		$html.= "</tr>\n";
		$html.= "</thead>\n";
		$html.= "<tbody>\n";

		$total = 0;

		foreach ($this->getItemArray() as $item) {

			if ($item->getItems()) {

				$html .= '<tr><td>Bundle: '
						 . $item->name . '</td></tr>' . "\n";

				foreach ($item->getItems() as $bundleItem) {

					$html .= '<tr><td>&nbsp;&nbsp;-&nbsp;' . $bundleItem->description . ' <span class="helvetica">&times;</span> ' . $bundleItem->quantity . '</td></tr>' . "\n";
				}

			} else {

				$html .= '<tr><td>' . $item->description . ' <span class="helvetica">&times;</span> ' . $item->quantity . '</td></tr>' . "\n";

			}
		}

		$address = $this->getAddress('delivery');

		$html.= "</tbody>\n";
		$html.= "</table>\n";

		$html.= "<address class=\"vcard\">\n";
		$html.= "<span class=\"fn\">".$address->name."</span><br />\n";
		$html.= "<span class=\"Street\">";
		$html.= str_replace("\n","<br />\n",$address->address);
		$html.= "</span><br />\n";
		if (!is_null($address->postcode)) {
			$html.= "<span class=\"Postal-Code\">";
			$html.= $address->postcode;
			$html.= "</span><br />\n";
		}
		$html.= "<span class=\"Country\">";
		$html.= $address->country;
		$html.= "</span>\n";
		$html.= "</address>\n";

		return $html;
	}


	public function getDespatchNoteHTML($despatchID) {
		$html = '';
		if ($despatch = $this->getDespatches($despatchID)) {
			$html.= "<dl>\n";
			$html.= "<dt>Order Number</dt>\n";
			$html.= "<dd>".$this->orderID."</dd>\n";
			$html.= "<dt>Order Date</dt>\n";
			$html.= "<dd>".$this->orderDate."</dd>\n";
			$html.= "<dt>Customer Name</dt>\n";
			$html.= "<dd>";
			$html.= "<a href=\"/admin/user/index.php?user_id=";
			$html.= $this->userID;
			$html.= "\">";
			$html.= $this->userName;
			$html.= "</a>";
			$html.= "</dd>\n";
			$html.= "<dt>Account Number</dt>\n";
			$html.= "<dd>".$this->userID."</dd>\n";
			$html.= "</dl>\n";

			$html.= "<h2>Items included in this shipment:</h2>\n";

			$html.= "<table>\n";
			$html.= "<tr>\n";
			$html.= "<th>Description</th>\n";
			$html.= "<th><abbr title=\"Stock Keeping Unit\">SKU</abbr></th>\n";
			$html.= "</tr>\n";

			foreach ($despatch->getItemIDs() as $itemID) {

				$item = $this->getItems($itemID);

				$html.= "<tr>\n";
				$html.= "<td>";
				$html.= $item->productName;
				$html.= ", ";
				$html.= $item->styleName;
				$html.= ", ";
				$html.= $item->sizeName;
				$html.= "</td>\n";
				$html.= "<td>";
				$html.= $item->unitName;
				$html.= "</td>\n";
				$html.= "</tr>\n";

			}

			$html.= "</table>\n";

			$html.= "<p>Total items in this order: <strong>" . count($this->getItems());

			$html.= "</strong></p>\n";

			$html.= "<p>Items in this shipment: <strong>";
			$html.= count($despatch->getItemIDs());
			$html.= "</strong></p>\n";

		}
		return $html;
	}


	protected function spacer($val) {
		return strlen($val) < 1 ? '&nbsp;' : $val;
	}

	public function getNotes($raisedFrom = false) {
		$this->notes->load();

		if($raisedFrom === false) {
			return $this->notes->getItems();
		}

		$notes = array();

		foreach($this->notes->getItems() as $note) {
			if($note->raisedFrom == $raisedFrom) {
				$notes[] = $note;
			}
		}

		return $notes;
	}

}