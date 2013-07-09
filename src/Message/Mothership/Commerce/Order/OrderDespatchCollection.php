<?php



class OrderDespatchCollection extends OrderCollection {

	
	const STATUS_PACKED   = 3;
	const STATUS_POSTAGED = 4;
	const STATUS_SHIPPED  = 5;
	
	
	protected $required = array(
		
	);
	
	
	public function add(OrderDespatch $item) {
		$this->items[$item->despatchID] = $item;
	}
	
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load() {
		if ($this->orderID) {
			$this->items = array();
			$DB = new DBquery;
			$query = 'SELECT '
				   . 'order_despatch.order_id           AS orderID, '
				   . 'despatch_id        AS despatchID, '
				   . 'despatch_type_id   AS typeID, '
				   . "IF (despatch_type_name IS NOT NULL, despatch_type_name, 'package') AS typeName, "
				   . 'despatch_code      AS code, '
				   . 'despatch_cost      AS cost, '
				   . 'despatch_weight    AS weight, '
				   . 'despatch_packing_slip AS packingSlip, '
				   . 'despatch_label_type AS labelType, '
				   . 'despatch_label_data AS labelData, '
				   . 'despatch_invoice AS invoice, '
				   . 'order_despatch_items.item_id            AS itemID, '
				   . 'IF (despatch_timestamp IS NOT NULL, UNIX_TIMESTAMP(despatch_timestamp), NULL) AS despatchTimestamp, '
				   . 'IF (despatch_timestamp IS NULL, 1, 0) AS updatable '
				   . 'FROM order_despatch '
				   . 'LEFT JOIN order_despatch_type USING (despatch_type_id) '
				   . 'LEFT JOIN order_despatch_items USING (despatch_id) '
				   . 'WHERE order_despatch.order_id = ' . $this->orderID . ' '
				   . 'GROUP BY order_despatch_items.item_id '
				   . 'ORDER BY order_despatch.despatch_id DESC';
			if ($DB->query($query)) {
				$despatchID = NULL;
				while ($data = $DB->row()) {
					if ($data['despatchID'] != $despatchID) {
						$despatchID = $data['despatchID'];
						$class = 'OrderDespatch' . ucfirst(toCamelCaps($data['typeName']));
						$item = new $class;
						$item->addData($data);
						$this->add($item);
					}
					
					if (is_numeric($data['itemID']) && isset($this->items[$data['despatchID']])) {
						$this->items[$data['despatchID']]->addItemID($data['itemID']);
					}
				}
			} else {
				throw new OrderException('Unable to load despatches');
			}
		}
	}

	
	//PASS INSERT QUERY BACK TO THE CALLING OBJECT
	public function getInsertQuery($orderID) {
		$query = array();
		$DB = new DBquery;
		foreach ($this->items as $item) {
			if (is_null($item->despatchID)) {
				$this->validate($item);
				//INSERT THE DESPATCH OBJECT
				$query[] = 'INSERT INTO order_despatch ('
						 . 'order_id, '
						 . 'despatch_code, '
						 . 'despatch_cost, '
						 . 'despatch_weight, '
						 . 'despatch_type_id, '
						 . 'despatch_timestamp '
						 . ') VALUES ('
						 . $orderID . ', '
						 . $DB->escape($item->code) . ', '
						 . $DB->null($item->cost) . ', '
						 . $DB->null($item->weight) . ', '
						 . $DB->null($item->type()) . ', '
						 . $DB->escape($item->despatchTimestamp) . ' '
						 . ');';
				//GRAB THE DESPATCH ID
				$query[] = 'SET @despatchID = LAST_INSERT_ID();';
				$inserts = array(
					'items'  =>array(),
					'status' => array()
				);
				foreach ($item->getItems() as $despatchItem) {
					
					//INSERT EACH OF THE DESPATCH ITEMS
					$inserts['items'][]  = '( @despatchID, ' . $despatchItem->itemID . ')';
					
					//UPDATE EACH DESPATCH ITEM TO PACKED
					$inserts['status'][] = '(' . $orderID . ',' . $despatchItem->itemID . ',' 
									   . self::STATUS_PACKED . ', ' 
									   . 'NOW(), ' 
									   . $DB->null($item->staffID) . ')';
				}
				if ($inserts['items']) {
					
					$query[] = 'INSERT INTO order_despatch_items (despatch_id, item_id) '
						     . 'VALUES ' . implode(',', $inserts['items']);
					
					$query[] = 'INSERT INTO order_item_status (order_id, item_id, status_id, status_datetime, staff_id) '
							 . 'VALUES ' . implode(',', $inserts['status']);
				}
				
			}
		}
		return $query;
	}
	
	

}



?>