<?php



class OrderAddressCollection extends OrderCollection {

	protected $required = array(
		//'typeID', type is a predefined property for an address
		'name',
		'address',
		'address_1',
/* 		'address_2', */
		'postcode',
		'country',
		'countryID'
	);
	
	
	//ADD ITEM TO ITEMS AND RETURN INDEX
	public function add(OrderAddress $address) {
		$this->items[$address->type()] = $address;
		return $address->type();
	}
	
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load() {
		if ($this->orderID) {
			$this->items = array();
			$DB = new DBquery;
			$query = 'SELECT '
				   . 'order_id           AS orderID, '
				   . 'address_name       AS name, '
				   . 'address_address    AS address, '
				   . 'address_address_1  AS address_1, '
				   . 'address_address_2  AS address_2, '
				   . 'address_town       AS town, '
				   . 'address_state      AS state, '
				   . 'address_state_id   AS stateID, '
				   . 'address_postcode   AS postcode, '
				   . 'address_country    AS country, '
				   . 'address_country_id AS countryID, '
				   . 'address_telephone  AS telephone, '
				   . 'address_type_name  AS typeName '
				   . 'FROM order_address '
				   . 'JOIN order_address_type USING (address_type_id) '
				   . 'WHERE order_id = ' . $this->orderID;
			if ($DB->query($query)) {
				while ($data = $DB->row()) {
					$class = 'OrderAddress' . ucfirst(strtolower($data['typeName']));
					$item = new $class;
					$item->addData($data);
					$this->add($item);
				}
			} else {
				throw new OrderException('Unable to load addresses');
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
				    . $DB->escape($item->name) . ', '
				    . $DB->escape($item->address) . ', '
				    . $DB->escape($item->address_1) . ', '
				    . $DB->escape($item->address_2) . ', '
					. $DB->escape($item->town) . ', '
				    . $DB->escape($item->postcode) . ', '
				    . $DB->escape($item->state) . ', '
				    . $DB->escape($item->stateID) . ', '
					. $DB->escape($item->country) . ', '
				    . $DB->escape($item->countryID) . ', '
				    . $DB->escape($item->telephone) . ', '
				    . $item->type();
		}
		if ($inserts) {
			$query = 'INSERT INTO order_address ('
				   . 'order_id, '
				   . 'address_name, '
				   . 'address_address, '
				   . 'address_address_1, '
				   . 'address_address_2, '
				   . 'address_town, '
				   . 'address_postcode, '
				   . 'address_state, '
				   . 'address_state_id, '
				   . 'address_country, '
				   . 'address_country_id, '
				   . 'address_telephone, '
				   . 'address_type_id '
				   . ') VALUES (' . implode('),(', $inserts) . ')';
		}
		return $query;
	}
	

}



?>