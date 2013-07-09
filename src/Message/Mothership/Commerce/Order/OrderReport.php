<?php


class OrderReport extends Report {

	protected $status;
	protected $dateFrom;
	protected $dateTo;
	protected $sortOrdersBy = 'date';
	
	
	public function __construct() {
		parent::__construct();
		$this->addTable('order_summary LEFT JOIN order_shipping USING (order_id) LEFT JOIN val_user USING (user_id) LEFT JOIN order_pos USING (order_id)');
		$this->order('order_datetime', true);
	}
	
	
	public function getOrderList() {
		$this->addField('order_id');
		$this->report = array();
		if (!$this->conditions) {
			$this->filter();
		}
		if ($this->runQuery()) {
			while ($orderID = $this->DB->value()) {
				$this->report[] = $orderID;
			}
		}
		
		return $this->report;
	}
	
	
	public function getTotalsByCurrency() {
		$this->addField('currency_id');
		$this->addField('SUM(((order_total - order_discount) + shipping_amount)) AS total');
		$this->group('currency_id');
		$this->report = array();
		if ($this->runQuery()) {
			while ($row = $this->DB->row()) {
				$this->report[$row['currency_id']] = $row['total'];
			}
		}
		return $this->report;
	}
	
	
	public function filter($filter = 'NONE') {
		switch ($filter) {
			
			case 'AWAITING_SHIPPING':
				$this->addCondition('status_id < ' . ORDER_STATUS_SHIPPED);
				break;
				
			case 'SHIPPED':
				$this->addCondition('status_id >= ' . ORDER_STATUS_SHIPPED);
				break;
			
			case 'ACTIVE':
				$this->addCondition('status_id > ' . ORDER_STATUS_ORDERED);
				$this->addCondition('(status_id < ' . ORDER_STATUS_SHIPPED . ' OR (status_id > 5 AND DATEDIFF(\'' . date('Y-m-d', (time() + 60*60*24)) . '\', order_updated) = 1))');
				break;
			
			case 'ORDERED':
			case 'PRINTED':
			case 'PICKED':
			case 'PACKED':
				$this->addCondition('status_id = ' . constant('ORDER_STATUS_' . $filter));
				break;

			case 'WEB':
				$this->addCondition('shop_id IS NULL');
				break;
				
			default: //NO FILTER : ALL ORDERS
				$this->addCondition('order_id IS NOT NULL');
		}
	}
	
	
	public function user($userID) {
		$this->addCondition('user_id = ' . (int) $userID);
	}
	
	
	public function dateRange($from, $to = NULL) {
		$pattern = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
		if (preg_match($pattern, $from)) {
			$this->dateFrom = $from . ' 00:00:01';
			$this->addCondition("order_datetime >= '" . $this->dateFrom . "'");
		}
		if ($to && preg_match($pattern, $to)) {
			$this->dateTo = $to . ' 23:59:59';
			$this->addCondition("order_datetime <= '" . $this->dateTo . "'");
		}	
	}
	
	
	
	
	/*
	protected function sortOrders() {
		$sorted = array();
		switch ($this->sortOrdersBy) {
		
			case 'total':
			case 'orderID':
			case 'userName':
				foreach ($this->orders as $order) {
					$sorted[$order->{$this->sortOrdersBy}] = $order;
				}
				break;
			
			default: //date placed
				foreach ($this->orders as $order) {
					$sorted[$order->placedTimestamp] = $order;
				}
		}
		//DIRECTION
		if ($this->desc) {
			krsort($sorted);
		} else {
			ksort($sorted);
		}
		
		return $sorted;
	}
	*/
	

}



?>