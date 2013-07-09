<?php


abstract class OrderCollection extends Collection {

	protected $orderID;
	
	
	public function __construct($orderID) {
		$this->orderID = (int) $orderID;
	}
	
	
	//SAVE NEW ITEMS
	public function commit() {
		if ($query = (array) $this->getInsertQuery($this->orderID)) {
			//START A TRANSACTION
			$trans = new DBtransaction;
			foreach ($query as $q) {
				$trans->add($q);
			}
			//RUN THE TRANSACTION
			if ($trans->run()) {
				$this->load();
			} else {
				throw new OrderException('error saving ' . get_class($this));
			}
		}
	}

}




?>