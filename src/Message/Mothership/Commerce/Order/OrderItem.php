<?php


class OrderItem extends Item {
	
	protected $statusID;
	protected $statusName;
	protected $statusDate;
	protected $staffID;
	
	protected $orderID;
	protected $itemID;
	protected $description;
	protected $descriptionLocalised;
	protected $pickingDescription;
	protected $originalPrice;
	protected $price;
	protected $discount;
	protected $rrp;
	protected $tax;
	protected $taxable;
	protected $taxCode;
	protected $taxRate;
	protected $weight;
	protected $note;
	protected $unitID;
	protected $unitName;
	protected $unitCost;
	protected $barcode;
	protected $productID;
	protected $productName;
	protected $styleID;
	protected $styleName;
	protected $sizeID;
	protected $sizeName;
	protected $brandID;
	protected $brandName;
	protected $supplierRef;
	protected $bundleKey;
	protected $catalogueID;
	protected $crossSoldFrom;

	// PERSONALISATION
	protected $senderName;
	protected $recipientName;
	protected $recipientEmail;
	protected $recipientMessage;
	
	protected $publicProperties = array(
		'orderID'              => 0,
		'itemID'               => 0,
		'description'          => '',
		'descriptionLocalised' => '',
		'pickingDescription'   => '',
		'originalPrice'        => 0.00,
		'price'                => 0.00,
		'discount'             => 0.00,
		'rrp'                  => 0.00,
		'tax'                  => 0.00, 
		'taxable'              => 0,  
		'taxCode'              => '',
		'taxRate'              => 0.00,
		'weight'               => 0,
		'note'                 => '',
		'unitID'               => 0,
		'unitName'             => '',
		'barcode'              => '',
		'unitCost'             => 0.00,
		'productID'            => 0,
		'productName'          => '',
		'styleID'              => 0.00,
		'styleName'            => '',
		'sizeID'               => 0,
		'sizeName'             => '',
		'brandID'              => 0,
		'brandName'            => '',
		'staffID'              => 0,
		'statusID'             => 0,
		'statusName'           => '',
		'statusDate'           => 0,
		'supplierRef'          => '',
		'bundleKey'            => 0,
		'catalogueID'          => 0,
		'crossSoldFrom'        => 0,

		// PERSONALISATION
		'senderName'           => '',
		'recipientName'        => '',
		'recipientEmail'       => '',
		'recipientMessage'     => '',
	);

	public function __construct($itemID = null) {
		$this->itemID = $itemID ? (int) $itemID : null;
		if ($this->itemID) {
			$this->getStatus();
		}
	}
	
	//ADD A DESCRIPTION ONCE THE DATA HAS BEEN ADDED
	public function addData($data) {
		parent::addData($data);
		$this->setItemDescription();
	}
		
	public function price($price)
	{
		$this->price = $price;
		if (!$this->originalPrice) {
			$this->originalPrice = $price;
		}
	}
	
	public function discount($amount)
	{
		$this->discount = round($amount, 2);
		if (!$this->itemID) {
			$this->calculateTax();
		}
	}

	//GENERATE A UNIQUE DESCRIPTION FOR THIS ITEM 
	protected function setItemDescription() {
		if (!$this->description) {
			$this->description = $this->productName;
			if (trim($this->styleName)) {
				$this->description .= ', ' . $this->styleName;
			}
			if (trim($this->sizeName)) {
				$this->description .= ', ' . $this->sizeName;
			}
		}
	}
	
	//CALCULATE THE CORRECT AMOUNT OF TAX FOR THIS ORDER
	public function calculateTax()
	{
		if ($this->taxable) {
			$this->tax = (($this->getPrice() - $this->discount) / (100 + $this->taxRate)) * $this->taxRate;
		} else {
			$this->price = round(($this->originalPrice - $this->discount) / (1 + ($this->taxRate / 100)) + $this->discount, 2);
		}
	}

	public function getTaxDiscount()
	{
		return round($this->originalPrice - $this->price, 2);
	}
		
	//UPDATE THE STATUS FOR THIS ORDER ITEM
	public function updateStatus($status, $staffID = NULL, $date = NULL) {
		$staffID = !empty($staffID) ? (int) $staffID : NULL;
		$DB = new DBquery;
		if (is_null($date)) {
			$date = 'NOW()';
		} else {
			$date = $DB->escape($date);
		}
		$query = 'REPLACE INTO order_item_status SET '
			   . 'order_id  = ' . $this->orderID . ', '
			   . 'item_id   = ' . $this->itemID . ', '
			   . 'status_id = ' . (int) $status . ', '
			   . 'staff_id  = ' . $DB->null($staffID) . ', '
			   . 'status_datetime = ' . $date;
		$DB->query($query);
		if ($DB->result()) {
			$this->getStatus();
		} else {
			throw new OrderException('Problem updating item status');
		}	   
	}
	
	//RELEASE HOLD STATUS
	public function releaseHold() {
		$DB = new DBquery;
		$query = 'DELETE FROM order_item_status WHERE '
			   . 'order_id  = ' . $this->orderID . ' AND '
			   . 'item_id   = ' . $this->itemID . ' AND '
			   . 'status_id = ' . ORDER_STATUS_ON_HOLD;
		$DB->query($query);
		if ($DB->error()) {
			throw new OrderException('Problem removing on hold order status');
		}
	}

	//FILTER OUT INCREMENTAL STATUS NAMES LEAVING ORDERED, AWAITING DESPATCH, SHIPPED
	public function shortStatus() {
		$status = 'Ordered';
		if ($this->statusID === ORDER_STATUS_PENDING) {
			$status = 'Pending full payment';
		}
		if ($this->statusID > 0) {
			$status = 'Awaiting shipping';
		}
		if ($this->statusID > 5) {
			$status = ucfirst($this->statusName);
		}
		return $status;	
	}
	
	//MISSING METHOD REFERENCED IN THIS CLASS (PERMITTED BY ITEM PARENT CLASS). PRESUMABLY REDUNDANT
	public function getStatus() {
		
	}

	public function returnable() {
		if($this->statusID < ORDER_STATUS_SHIPPED || $this->statusID >= ORDER_STATUS_RETURNED) {
			return false;
		}
		return true;
	}

	public function getPrice() {
		return $this->originalPrice ?: $this->price;
	}

	public function isPersonalised()
	{
		return ($this->senderName || $this->recipientName || $this->recipientEmail || $this->recipientMessage);
	}
	
}