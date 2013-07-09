<?php



class OrderReturnCollection extends OrderCollection {
	
	protected $required = array(
		'itemID',
		'reasonID',
		'statusID'
	);
	
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load() {
		if ($this->orderID) {
			$this->items = array();
			$DB = new DBquery;
			$query = 'SELECT '
			   	   . 'order_id                        AS orderID, '
				   . 'return_id                       AS returnID, '
			       . 'item_id                         AS itemID, '
			       . 'UNIX_TIMESTAMP(return_datetime) AS returnTimestamp, '
			       . 'return_reason_id                AS reasonID, '
				   . 'return_reason_name              AS reasonName, '
				   . 'return_status_id                AS statusID, '
				   . 'return_status_name              AS statusName, '
				   . 'return_resolution_id            AS resolutionID, '
				   . 'return_resolution_name          AS resolutionName, '
				   . 'return_exchange_item_id         AS exchangeItemID, '
				   . 'refund_id                       AS refundID, '
				   . 'balancing_payment               AS balancingPayment, '
				   . 'package_received_date           AS packageReceivedDate, '
				   . 'return_destination_id           AS destinationID, '
				   . 'return_destination_name         AS destinationName, '
				   . 'location_id                     AS destinationLocationID, '
				   . 'location.name                   AS destinationLocationName, '
				   . 'accepted                        AS accepted '
				   . 'FROM order_item_return '
			       . 'JOIN order_return_reason USING (return_reason_id) '
				   . 'JOIN order_return_status_name USING (return_status_id) '
				   . 'JOIN order_return_resolution USING (return_resolution_id) '
				   . 'LEFT JOIN order_return_destination USING (return_destination_id) '
				   . 'LEFT JOIN location USING (location_id) '
				   . 'WHERE order_id = ' . $this->orderID . ' '
				   . 'ORDER BY return_id DESC';
			if ($DB->query($query)) {
				while ($data = $DB->row()) {
					$item = new OrderReturn;
					$item->addData($data);
					$this->add($item);
				}
			} else {
				echo $DB->error();
				throw new OrderException('Unable to load returns');
			}
		}
	}
	

	//PASS INSERT QUERY BACK TO THE CALLING OBJECT
	public function getInsertQuery($orderID) {
		$query = array();
		$DB = new DBquery;
		$inserts = array();
		foreach ($this->items as $item) {
			if (is_null($item->returnID)) {
				$this->validate($item);
				$inserts[] = $orderID . ', '
						. $DB->null($item->itemID) . ', '
						. 'NOW(), '
						. $DB->null($item->reasonID) . ', '
						. $DB->null($item->statusID) . ', '
						. $DB->null($item->resolutionID) . ', '
						. $DB->null($item->exchangeItemID) . ', '
						. $DB->null($item->refundID) . ', '
						. $DB->null($item->destinationID) . ', '
						. $DB->null($item->accepted) . ', '
						. $DB->escape($item->packageReceivedDate) . ', '
						. $DB->null($item->balancingPayment);
						
				//DETERMINE ITEM STATUS
				$status = ORDER_STATUS_RETURNED; //returned
				if ($item->refundID) {
					$status = ORDER_STATUS_REFUNDED;
				} elseif ($item->exchangeItemID) {
					$status = ORDER_STATUS_EXCHANGED;
				}
				
				$query[] = 'INSERT IGNORE INTO order_item_status SET '
					     . 'order_id  = ' . $this->orderID . ', '
					     . 'item_id   = ' . $item->itemID . ', '
					     . 'status_id = ' . (int) $status . ', '
					     . 'staff_id  = NULL, '
					     . 'status_datetime = NOW()';
			}
		}
		if ($inserts) {
			$query[] = 'INSERT INTO order_item_return ('
				   . 'order_id, '
				   . 'item_id, '
				   . 'return_datetime, '
				   . 'return_reason_id, '
				   . 'return_status_id, '
				   . 'return_resolution_id, '
				   . 'return_exchange_item_id, '
				   . 'refund_id, '
				   . 'return_destination_id, '
				   . 'accepted, '
				   . 'package_received_date, '
				   . 'balancing_payment '
				   . ') VALUES (' . implode('),(', $inserts) . ')';
		}
		return $query;
	}
	
	
	//DELETE A RETURN FROM THE COLLECTION
	public function delete($returnID) {
		foreach ($this->items as $return) {
			if ($return->returnID == $returnID && $return->statusID < RETURN_STATUS_PAID) {
				$trans = new DBtransaction;
				$trans->add('DELETE FROM order_item_status WHERE item_id = ' . $return->itemID . ' AND status_id > ' . ORDER_STATUS_SHIPPED);
				$trans->add('DELETE FROM order_item_return WHERE return_id = ' . $return->returnID);
				if ($return->exchangeItemID) {
					$trans->add('DELETE FROM order_item WHERE item_id = ' . $return->exchangeItemID);
					$trans->add('DELETE FROM order_item_status WHERE item_id = ' . $return->exchangeItemID);
				}
				return $trans->run();
			}
		}
	}
		

}



?>