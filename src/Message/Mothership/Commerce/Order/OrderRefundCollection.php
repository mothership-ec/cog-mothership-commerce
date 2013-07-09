<?php



class OrderRefundCollection extends OrderCollection {
	
	protected $required = array(
		'reasonID',
		'amount'
	);
	
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load() {
		if ($this->orderID) {
			$this->items = array();
			$DB = new DBquery;
			$query = 'SELECT '
			   	   . 'refund_id          AS refundID, '
			       . 'refund_reason_id   AS reasonID, '
			       . 'payment_type_id    AS typeID, '
			       . 'payment_type_name  AS typeName, '
			       . 'refund_reason_id   AS reasonID, '
			       . 'refund_amount      AS amount, '
			       . 'refund_reference   AS reference, '
			       . 'refund_reason_name AS reasonName '
			       . 'FROM order_refund '
			       . 'JOIN order_refund_reason USING (refund_reason_id) '
			       . 'LEFT JOIN order_payment_type USING (payment_type_id) '
			       . 'WHERE order_id = ' . $this->orderID . ' '
				   . 'ORDER BY refund_id DESC';
			if ($DB->query($query)) {
				while ($data = $DB->row()) {
					$item = new OrderRefund;
					$item->addData($data);
					$this->add($item);
				}
			} else {
				throw new OrderException('Unable to load refunds');
			}
		}
	}
	

	//PASS INSERT QUERY BACK TO THE CALLING OBJECT
	public function getInsertQuery($orderID) {
		$query = NULL;
		$DB = new DBquery;
		$inserts = array();
		foreach ($this->items as $item) {
			if (is_null($item->refundID)) {
				$this->validate($item);
				$inserts[] = $orderID . ', '
						. $DB->null($item->reasonID) . ', '
						. $DB->null($item->typeID) . ', '
						. $DB->null($item->amount) . ', '
						. $DB->escape($item->reference);
			}
		}
		
		if ($inserts) {
			$query = 'INSERT INTO order_refund ('
				   . 'order_id, '
				   . 'refund_reason_id, '
				   . 'payment_type_id, '
				   . 'refund_amount, '
				   . 'refund_reference '
				   . ') VALUES (' . implode('),(', $inserts) . ')';
		}
		return $query;
	}
	

}



?>