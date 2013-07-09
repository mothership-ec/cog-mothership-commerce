<?php



class OrderDiscountCollection extends OrderCollection {
	
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load() {
		if ($this->orderID) {
			$this->items = array();
			$DB = new DBquery;
			$query = 'SELECT '
			   	   . 'discount_id          AS discountID, '
			       . 'discount_type_id     AS typeID, '
			       . 'discount_amount      AS amount, '
			       . 'discount_percentage  AS percentage, '
			       . 'discount_type_name   AS typeName '
			       . 'FROM order_discount '
			       . 'JOIN order_discount_type USING (discount_type_id) '
			       . 'WHERE order_id = ' . $this->orderID . ' ';
			if ($DB->query($query)) {
				while ($data = $DB->row()) {
					$class = 'OrderDiscount' . ucfirst(toCamelCaps(str_replace(' ', '_', $data['typeName'])));
					$item = new $class;
					$item->addData($data);
					$this->add($item);
				}
			} else {
				throw new OrderException('Unable to load discounts');
			}
		}
	}
	

	//PASS INSERT QUERY BACK TO THE CALLING OBJECT
	public function getInsertQuery($orderID) {
		$query = NULL;
		$DB = new DBquery;
		$inserts = array();
		foreach ($this->items as $item) {
			$this->validate($item);
			$inserts[] = $orderID . ', '
					. $DB->escape($item->discountID) . ', '
				    . $item->type() . ', '
				    . $DB->null($item->amount) . ', '
				    . $DB->null($item->percentage);
		}
		if ($inserts) {
			$query = 'INSERT INTO order_discount ('
				   . 'order_id, '
				   . 'discount_id, '
				   . 'discount_type_id, '
				   . 'discount_amount, '
				   . 'discount_percentage '
				   . ') VALUES (' . implode('),(', $inserts) . ')';
		}
		return $query;
	}
	
	
}



?>