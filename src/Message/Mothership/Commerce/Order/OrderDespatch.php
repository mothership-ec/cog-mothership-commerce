<?php


abstract class OrderDespatch extends Item {

	protected $orderID;
	protected $despatchID;
	protected $typeID;
	protected $typeName;
	protected $code;
	protected $cost;
	protected $weight;
	protected $statusID;
	protected $itemIDs = array();
	protected $orderItems;
	protected $staffID;
	protected $despatchTimestamp;
	protected $updatable;
	protected $packingSlip;
	protected $invoice;
	protected $labelType;
	protected $labelData;


	protected $publicProperties = array(

		'orderID'           => 0,
	    'despatchID'        => 0,
		'typeName'          => '',
		'code'              => '',
	    'cost'              => 0.00,
		'staffID'           => 0,
		'weight'            => 0.00,
		'despatchTimestamp' => '',
		'packingSlip'       => '',
		'labelType'         => '',
		'labelData'         => '',
		'invoice'           => ''

	);


	public function __construct() {
		$this->setType();
	}

	abstract protected function setType();

	public function type() {
		return $this->typeID;
	}


	public function despatchTimestamp($val) {
		if (is_null($this->despatchTimestamp)) {
			$this->despatchTimestamp = $val;
		}
	}

	public function updatable($val) {
		if (is_null($this->updatable)) {
			$this->updatable = (bool) $val;
		}
	}

	public function addItemID($itemID) {
		$this->itemIDs[] = (int) $itemID;
	}

	public function addItem(OrderItem $item) {
		$this->orderItems[$item->itemID] = $item;
		$this->itemIDs[] = $item->itemID;
		$this->addWeight($item->weight);
	}

	public function getItemIDs() {
		return $this->itemIDs;
	}

	public function getItems() {
		return $this->orderItems;
	}


	public function code($code) {
		if (is_null($this->code) && !is_null($code)) {
			$this->code = $code;
		}
	}

	public function getCustomsValue() {
		$order = new Order($this->orderID);
		$value = 0;
		foreach ($this->getItemIDs() as $itemID) {
			$item = $order->getItems($itemID);
			$value += $item->price;
		}
		return $value - (($value / $order->getsubTotal()) * $order->getDiscount());
	}


	public function postable() {
		return (is_null($this->code) && is_null($this->despatchTimestamp));
	}


	public function pickupable() {
		return (!$this->postable() && is_null($this->despatchTimestamp));
	}


	protected function addWeight($weight) {
		if (is_null($this->weight)) {
			$this->weight = 0;
		}
		$this->weight += (float) $weight;
	}


	abstract public function getTrackingLink(); //returns object ->href, ->text

	public function addPackingSlip() {
		$doc = new OrderDocumentPackingslip($this->orderID, ORDER_STATUS_PACKED, array('despatchID' => $this->despatchID));
		$DB =  new DBquery;
		$query = 'UPDATE order_despatch '
			   . 'SET despatch_packing_slip = ' . $DB->escape($doc->getDocument()) . ' '
			   . 'WHERE despatch_id = ' . $this->despatchID;
		return $DB->query($query);
	}


	public function update() {
		if (!$this->updatable) {
			throw new OrderException('Despatch ' . $this->despatchID . ' for order ' . $this->orderID . ' has already been shipped');
		}
		$status = ORDER_STATUS_PACKED;
		if ($this->pickupable()) {
			$status = ORDER_STATUS_POSTAGED;
		} elseif (!is_null($this->despatchTimestamp)) {
			$status = ORDER_STATUS_SHIPPED;
		}
		$trans =  new DBtransaction;
		$trans->add('UPDATE order_despatch SET ' .
				    'despatch_code      = ' . $trans->escape($this->code) . ', ' .
					'despatch_cost      = ' . $trans->null($this->cost) . ', ' .
					'despatch_timestamp = ' . $trans->null($this->despatchTimestamp) . ' ' .
					'WHERE despatch_id  = ' . $this->despatchID . ' ' .
					'AND despatch_timestamp IS NULL');
		foreach ($this->getItemIDs() as $itemID) {
			$trans->add('INSERT IGNORE INTO order_item_status SET ' .
					    'order_id  = ' . $this->orderID . ', ' .
						'item_id   = ' . $itemID . ', ' .
						'status_id = ' . (int) $status . ', ' .
						'staff_id  = ' . $trans->null($this->staffID) . ', ' .
						'status_datetime = ' . ($this->despatchTimestamp ? $this->despatchTimestamp : 'NOW()'));
		}
		return $trans->run();
	}


}


?>