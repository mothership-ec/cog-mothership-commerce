<?php



class OrderRepairCollection extends OrderCollection {
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load() {
		if ($this->orderID && !$this->items) {
			$DB = new DBquery;
			$query = "
				SELECT
					repair_id 		AS repairID, 
					order_id 		AS orderID,
					order_item_id 	AS orderItemID,
					catalogue_id 	AS catalogueID,
					product_name 	AS productName,
					repair_notes 	AS notes,
					UNIX_TIMESTAMP(repair_purchase_date) AS purchaseDate,
					repair_retailer AS retailer,
					repair_faulty 	AS faulty
				FROM
					order_repair
				WHERE
					order_id = ".$this->orderID."
			";
				
			if ($DB->query($query)) {
				while ($data = $DB->row()) {
					$item = new OrderRepair;
					foreach($data as $key => $val) {
						$item->{$key} = $val;
					}

					// Get the items in this repair
					$DB2 = new DBquery;
					$DB2->query("
						SELECT
							repair_id AS repairID, 
							service_id AS serviceID, 
							price AS price,
							service_name AS name,
							service_description AS description
						FROM
							order_repair_option
						WHERE
							repair_id = ".$data['repairID']."
					");

					while ($option = $DB2->row()) {
						$item->addOption((object)$option);
					}

					$this->add($item);
				}
			} else {
				//dump($DB->error());
				throw new OrderException('Unable to load repair items');
			}
		}
	}



	//PASS INSERT QUERY BACK TO THE CALLING OBJECT
	public function getInsertQuery($orderID) {
		$DB = new DBquery;
		$inserts = array();
		foreach ($this->items as $item) {
			if (is_null($item->repairID)) {
				$product = new Product( new CatalogueItem($item->catalogueID) );
				$inserts[] = "
					INSERT INTO `order_repair` (`order_id`, `order_item_id`, `catalogue_id`, `product_name`, `repair_notes`, `repair_purchase_date`, `repair_retailer`, `repair_faulty`)
					VALUES
						(
							".$orderID.", 
							".$DB->null($item->orderItemID).", 
							".$DB->null($item->catalogueID).", 
							".$DB->escape($product->productName).",
							".$DB->escape($item->notes).",
							FROM_UNIXTIME(".$item->purchaseDate."),
							".(int)$item->retailer.",
							".$DB->null($item->faulty)."
						);
				";

				$inserts[] = 'SET @REPAIR_ID = LAST_INSERT_ID();';

				foreach($item->getOptions() as $option) {
					$inserts[] = "
						INSERT INTO `order_repair_option` (`repair_id`, `service_id`, `price`, `service_name`, `service_description`)
							VALUES
								(@REPAIR_ID, ".$DB->null($option->serviceID).", ".$DB->null($option->price).", ".$DB->escape($option->name).", ".$DB->escape($option->description).");
					";
				}
			}
		}
		return $inserts;
	}

}



?>