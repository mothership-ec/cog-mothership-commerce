<?php


define('RETURN_REASON_EXCHANGED', 4);


class OrderReturn extends Item {

	protected $orderID;
	protected $returnID;
	protected $itemID;
	protected $reasonID;
	protected $reasonName;
	protected $statusID;
	protected $statusName;
	protected $resolutionID;
	protected $resolutionName;
	protected $returnTimestamp;
	protected $exchangeItemID;
	protected $refundID;
	protected $balancingPayment;
	protected $packageReceivedDate;
	protected $destinationID;
	protected $destinationName;
	protected $destinationLocationID;
	protected $destinationLocationName;
	protected $accepted;
	
	protected $publicProperties = array(
			
		'orderID'             => 0,
	    'returnID'            => 0,
		'itemID'              => 0,
		'reasonID'            => 0,
		'reasonName'          => '',
		'statusID'            => 0,
		'statusName'          => '',
		'resolutionID'        => 0,
		'resolutionName'      => '',
		'returnTimestamp'     => 0,
		'exchangeItemID'      => 0,
		'refundID'            => 0,
		'balancingPayment'    => 0.0,
		'packageReceivedDate' => '',
		'destinationID'       => 0,
		'destinationName'     => '',
		'destinationLocationID' => 0,
		'destinationLocationName' => '',
		'accepted'            => 0,
	);

	public $itemKey; // used for tracking return item in standalone returns
	
	
	public function __construct($itemID = NULL, $reasonID = NULL, $statusID = NULL) {
		$this->itemID($itemID);
		$this->reasonID($reasonID);
	}
	
	
	public function isComplete() {
		return ($this->statusID == RETURN_STATUS_COMPLETE);
	}
	
	
	public function isSentToDimensions() {
		
		$DB = new DBquery;
		
		$DB->query('SELECT return_id FROM order_return_dimensions WHERE order_id = ' . (int) $this->orderID . ' AND return_id = ' . (int) $this->returnID);
		
		return ($DB->numrows() == 0 ? false : true);
		
	}
	
	
	protected function returnTimestamp($val) {
		$this->returnTimestamp = (int) $val;
	}
	
	public function update() {
		$trans =  new DBtransaction;
		$sql = 'UPDATE order_item_return SET ' .
					'order_id                   = ' . $trans->null($this->orderID) . ', ' .
					'item_id                    = ' . $trans->null($this->itemID) . ', ' .
					'return_reason_id           = ' . $trans->null($this->reasonID) . ', ' .
					'return_status_id           = ' . $trans->null($this->statusID) . ', ' .
					'return_resolution_id       = ' . $trans->null($this->resolutionID) . ', ' .
					'return_exchange_item_id    = ' . $trans->null($this->exchangeItemID) . ', ' .
					'refund_id                  = ' . $trans->null($this->refundID) . ', ' .
					'balancing_payment          = ' . $trans->null($this->balancingPayment) . ', ' .
					'package_received_date      = ' . $trans->escape($this->packageReceivedDate) . ', ' .
					'return_destination_id      = ' . $trans->null($this->destinationID) . ', ' .
					'accepted                   = ' . $trans->null($this->accepted) . ' ' .
		'WHERE return_id  = ' . $this->returnID . ' '
					;
//		echo "sql: $sql<br/>";exit;//TMP					
		$trans->add($sql);					
		if ($trans->run() && $this->isComplete()) {
			$order = new OrderUpdate($this->orderID);
			$order->completeReturn($this->returnID);
			unset($order);
		}
		return $trans->result();
	}
	

}


?>