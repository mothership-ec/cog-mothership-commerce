<?php



class OrderNoteCollection extends OrderCollection {
	
	protected $required = array(
		'note',
		'userID',
		'raisedFrom',
	);
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load() {
		if ($this->orderID) {
			$this->items = array();
			$DB = new DBquery;
			$query = "
				SELECT
					note_id AS noteID,
					order_id AS orderID,
					user_id AS userID, 
					CONCAT(user_forename, ' ', user_surname) AS userName,
					note_datetime AS datetime, 
					notify_customer AS notifyCustomer, 
					note,
					raised_from AS raisedFrom
				FROM
					order_note
					LEFT JOIN val_user USING (user_id)
				WHERE 
					order_id = ".$this->orderID."
				ORDER BY 
					order_id
			";

			if ($DB->query($query)) {
				while ($data = $DB->row()) {
					$item = new OrderNote;
					$item->addData($data);
					$this->add($item);
				}
			} else {
				throw new OrderException('Unable to load order notes');
			}
		}
	}
	

	//PASS INSERT QUERY BACK TO THE CALLING OBJECT
	public function getInsertQuery($orderID) {
		$query = NULL;
		$DB = new DBquery;
		$inserts = array();
		foreach ($this->items as $item) {
			if (is_null($item->noteID)) {
				$this->validate($item);
				$inserts[] = implode(', ', array(
					$orderID,
					$DB->null($item->userID),
					'NOW()',
					(int)$item->notifyCustomer,
					$DB->escape($item->note),
					$DB->escape($item->raisedFrom),
				));
			}
		}
		
		if ($inserts) {
			$query = "
				INSERT INTO 
					`order_note` (`order_id`, `user_id`, `note_datetime`, `notify_customer`, `note`, `raised_from`)
				VALUES
					(" . implode('),(', $inserts) . ")
			";
		}

		return $query;
	}
}
