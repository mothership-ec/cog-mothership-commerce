<?php



class OrderPaymentCollection extends OrderCollection {
	
	protected $required = array(
		'amount'
	);
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load() {
		if ($this->orderID) {
			$this->items = array();
			$DB = new DBquery;
			$query = 'SELECT '
			   	   . 'payment_id                          AS paymentID, '
			       . 'payment_type_id                     AS typeID, '
			       . 'payment_amount                      AS amount, '
			       . 'payment_reference                   AS reference, '
			       . 'payment_datetime                    AS datetime, '
			       . 'UNIX_TIMESTAMP(payment_datetime)    AS timestamp, '
			       . 'payment_type_name                   AS typeName, '
				   . 'payment_note                        AS note '
			       . 'FROM order_payment '
			       . 'JOIN order_payment_type USING (payment_type_id) '
			       . 'WHERE order_id = ' . $this->orderID . ' '
				   . 'ORDER BY payment_id DESC';
			if ($DB->query($query)) {
				while ($data = $DB->row()) {
					$class = 'OrderPayment' . ucfirst(toCamelCaps(str_replace(' ', '_', $data['typeName'])));
					$item = new $class;
					$item->addData($data);
					$this->add($item);
				}
			} else {
				throw new OrderException('Unable to load payments');
			}
		}
	}
	

	//PASS INSERT QUERY BACK TO THE CALLING OBJECT
	public function getInsertQuery($orderID) {
		$query = NULL;
		$DB = new DBquery;
		$inserts = array();
		foreach ($this->items as $item) {
			if (is_null($item->paymentID)) {
				$this->validate($item);
				$inserts[] = $orderID . ', '
					    . $item->type() . ', '
					    . 'NOW(), '
					    . $DB->null($item->amount) . ', '
					    . $DB->escape($item->reference) . ', '
						. $DB->escape($item->note);
			}
		}
		if ($inserts) {
			$query = 'INSERT INTO order_payment ('
				   . 'order_id, '
				   . 'payment_type_id, '
				   . 'payment_datetime, '
				   . 'payment_amount, '
				   . 'payment_reference, '
				   . 'payment_note '
				   . ') VALUES (' . implode('),(', $inserts) . ')';
		}
		return $query;
	}

}



?>