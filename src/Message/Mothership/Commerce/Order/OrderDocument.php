<?php


abstract class OrderDocument {
	
	
	protected $order;
	protected $params;
	protected $status;
	protected $statusString;
	protected static $itemStatusLabels = array(
		'sent'      => 'Items previously delivered',
		'included'  => 'Items included in this package',
		'returned'  => 'Items returned, exchanged or refunded',
		'remaining' => 'Items remaining to be delivered'
	);

	
	public function __construct($orderID, $status = NULL, $params = NULL) {
		$this->order  = new Order($orderID);
		$this->status = $status;
		//SAVE ANY ADDITIONAL PARAMETERS PASSED TO THE DOCUMENT
		$this->params = $params;
	}
	
	
	//MAKE ORDER PROPERTIES AVAILABLE
	public function __get($p) {
		return $this->order->{$p};
	}
	
	
	public function getTotal() {
		return $this->order->getTotal();
	}
	
	
	abstract public function getDocument();

	
	protected function groupItems() {
		$order = array(
			'included'  => array(),
			'sent'      => array(),
			'returned'  => array(),
			'remaining' => array()
		);
		foreach ($this->order->getItems() as $item) {
			
			switch ($item->statusID) {
				
				case $item->statusID > ORDER_STATUS_SHIPPED:
					$array = 'returned';
					break;
				
				case $item->statusID == ORDER_STATUS_SHIPPED:
					$array = 'sent';
					break;
				
				case $item->statusID >= $this->status:
					$array = 'included';
					break;
				
				default:
					$array = 'remaining';
				
			}
			$order[$array][$item->unitID][] = $item;
		}
		return $order;
	}
	
	
	protected function getItemListing() {
		
		$order = $this->groupItems();
		$this->setOrderStatusString($order);
		
		$html = '';

		foreach ($order as $status => $items) {

			if ($items) {
				$html .= '
				<div class="status-' . $status . '">
				<h3>' . self::$itemStatusLabels[$status] . '</h3>
				<table class="items-list">
				<thead>
					<tr>
						<th>Barcode</th>
						<th>Brand</th>
						<th>Description</th>
					</tr>
				</thead>
				<tbody>
				';

				foreach ($items as $unitID => $units) {

					for	($i = 0; $i < count($units); $i++) {
						
						$html .= '
						<tr>
							<td>'.$units[0]->barcode.'</td>
							<td>'.$units[0]->brandName.'</td>
							<td>' . ($units[0]->pickingDescription != "" ? $units[0]->description."<p class='picking-description'>".$units[0]->pickingDescription."</p>" : $units[0]->description)  . '</td>
							
						</tr>' . "\n";
					}
				}
				$html .= '</tbody></table></div>' . "\n";
			}
		}
		
		return $html;
		
	}


	protected function getItemListingUngrouped()
	{
		$orderItems = $this->groupItems();
		$html = '';

		foreach ($orderItems as $status => $items) {
			foreach ($items as $units) {
				foreach ($units as $item) {
					$html .= '
						<tr>
							<td class="four col">' . $item->description . '</td>
							<td class="col"><b>' . $status . '</b></td>
							<td class="col">' . count($units) . '</td>
						</tr>
					';
				}
			}
		}

		return $html;
	}
	
	
	protected function getOrderStatusString() {
		return $this->statusString;
	}
	
	
	protected function setOrderStatusString($items) {
		if (count($items['remaining']) < 1) {
			$this->statusString = 'This order is now complete. Thank you';
		} else {
			$this->statusString = '';
		}
	}
	
	public function getOrder() {
		return $this->order;
	}
	
	

}






?>