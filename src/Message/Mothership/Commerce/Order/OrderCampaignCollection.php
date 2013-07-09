<?php



class OrderCampaignCollection extends OrderCollection {
	
	protected $required = array(
		'code',
		'campaignID',
		'name',
		'threshold',
		'productIDs',
		'freeDelivery'
	);
	
	public function add($item) {
		
		//IF THE PASSED IN CAMPAIGN IS A CAMPAIGN OBJECT, CONVERT TO ORDERCAMPAIGN OBJECT
		if ($item instanceof Campaign) {
			$campaign = $item;
			$item = new OrderCampaign;
			$item->campaignID($campaign->getID());
			$item->code($campaign->getCode());
			$item->name($campaign->getName());
			$item->description($campaign->getDescription());
			$item->threshold($campaign->getThreshold());
			$item->productIDs(implode(',', $campaign->getProductIDs()));
			$item->freeDelivery($campaign->hasFreeDelivery());
		}
		
		//USE THE PARENT ADD TO ADD THE ORDERCAMPAIGN TO THE COLLECTION
		parent::add($item);
	}
	
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load() {
		if ($this->orderID) {
			$this->items = array();
			$DB = new DBquery;
			$query = 'SELECT '
			   	   . 'order_id               AS orderID, '
				   . 'campaign_id            AS campaignID, '
			       . 'campaign_code          AS code, '
			       . 'campaign_name          AS name, '
			       . 'campaign_description   AS description, '
			       . 'campaign_threshold     AS threshold, '
			       . 'campaign_product_ids   AS productIDs,'
				   . 'campaign_free_delivery AS freeDelivery '
				   . 'FROM order_campaign '
			       . 'WHERE order_id = ' . $this->orderID . ' ';
			if ($DB->query($query)) {
				while ($data = $DB->row()) {
					$item = new OrderCampaign;
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
		$inserts = array();
		foreach ($this->items as $item) {
			$this->validate($item);
			$inserts[] = $orderID . ', '
					. $DB->null($item->campaignID) . ', '
				    . $DB->escape($item->code) . ', '
				    . $DB->escape($item->name) . ', '
				    . $DB->escape($item->description) . ', '
					. $DB->null($item->threshold) . ', '
					. $DB->escape($item->productIDs) . ', '
					. (int) $item->freeDelivery;
		}
		if ($inserts) {
			$query = 'INSERT INTO order_campaign ('
				   . 'order_id, '
				   . 'campaign_id, '
				   . 'campaign_code, '
				   . 'campaign_name, '
				   . 'campaign_description, '
				   . 'campaign_threshold, '
				   . 'campaign_product_ids, '
				   . 'campaign_free_delivery '
				   . ') VALUES (' . implode('),(', $inserts) . ')';
		}			   
		return $query;
	}

}



?>