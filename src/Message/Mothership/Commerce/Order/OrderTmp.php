<?php


class OrderTmp {


	protected $orderKey;
	protected $fb;
	
	
	public function __construct() {
		$this->orderKey(session_id());
		$this->fb = Feedback::getInstance();
	}
	
	
	public function save(Order $order) {
		$DB = new DBquery;
		$query = 'REPLACE INTO order_tmp (session_id, order_object, session_timestamp) VALUES ('
	   		   . $DB->escape($this->orderKey) . ',' . $DB->escape(serialize($order)) . ', NOW() )';
		if (!$DB->query($query)) {
			$this->fb->addError('Problem saving order');
		}
		return $DB->result() ? $this->orderKey : NULL;
	}
	
	
	public function retrieve($key) {
		$order = NULL;
		$DB = new DBquery;
		$query = 'SELECT order_object FROM order_tmp WHERE session_id = ' . $DB->escape($key);
		if ($DB->query($query)) {
			$order = unserialize($DB->value());
			if ($order instanceof Order) {
				//INITIALISE ORDER TO REVIVE FEEDBACK, ETC
				$order->init();
				$this->cleanup();
			}
		} else {
			$this->fb->addError('Problem retrieving order: ' . $key);
		}
		return $order;
	}
	
	
	public function delete($key) {
		$DB = new DBquery;
		$query = 'DELETE FROM order_tmp WHERE session_id = ' . $DB->escape($key);
		if ($DB->query($query)) {
			return true;
		}
		return false;
	}
	
	
	public function clear() {
		$this->cleanup();
	}
	
	
	//CLEAN UP ALL EXCEPT TODAYS TMP ORDERS
	protected function cleanup() {
		$DB = new DBquery;
		$query = 'DELETE FROM order_tmp WHERE DATE_ADD(session_timestamp, INTERVAL 5 DAY) < NOW()';
		if (!$DB->query($query)) {
			$this->fb->addError('Problem deleting tmp order');
		}
		return $DB->result();
	}
	
	
	protected function orderKey($key) {
		$this->orderKey = (string) $key;
	}
	
	public function getOrderKey() {
		return $this->orderKey;
	}
	
}






?>