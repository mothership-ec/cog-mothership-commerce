<?php



class OrderBundleCollection extends OrderCollection {
	
	protected $required = array(
		'bundleID',
		'name',
		'descriptionLocalised',
		'price',
		'bundleKey'
	);
	
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load() {
		if ($this->orderID) {
			$this->items = array();
			$DB = new DBquery;
			$query = 'SELECT '
				   . 'bundle_id       AS bundleID, '
				   . 'bundle_name     AS name, '
				   . 'bundle_name_localised  AS descriptionLocalised, '
				   . 'bundle_key      AS bundleKey, '
				   . 'bundle_price    AS price, '
				   . 'bundle_tax      AS tax, '
				   . 'bundle_tax_code AS taxCode, '
				   . 'bundle_tax_rate AS taxRate '
				   . 'FROM order_bundle '
				   . 'WHERE order_id = ' . $this->orderID;
			if ($DB->query($query)) {
				while ($data = $DB->row()) {
					$item = new OrderBundle;
					$item->addData($data);
					$this->add($item);
				}
			} else {
				throw new OrderException('Unable to load bundles');
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
				    . $DB->escape($item->bundleID) . ', '
				    . $DB->escape($item->name) . ', '
					. $DB->escape($item->descriptionLocalised) . ', '
				    . $DB->escape($item->bundleKey) . ', '
				    . $DB->escape($item->price) . ', '
					. $DB->escape($item->tax) . ', '
					. $DB->escape($item->taxCode) . ', '
					. $DB->escape($item->taxRate) . ' ';
		}
		if ($inserts) {
			$query = 'INSERT INTO order_bundle ('
				   . 'order_id, '
				   . 'bundle_id, '
				   . 'bundle_name, '
				   . 'bundle_name_localised, '
				   . 'bundle_key, '
				   . 'bundle_price, '
				   . 'bundle_tax, '
				   . 'bundle_tax_code, '
				   . 'bundle_tax_rate '
				   . ') VALUES (' . implode('),(', $inserts) . ')';
		}
		return $query;
	}

}



?>