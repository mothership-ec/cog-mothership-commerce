<?php


class OrderCampaign extends Item {

	protected $orderID;
	protected $campaignID;
	protected $code;
	protected $name;
	protected $description;
	protected $productIDs;
	protected $freeDelivery;
	
	protected $publicProperties = array(
		
		'orderID'      => 0,
		'campaignID'   => 0,
		'code'         => '',
		'name'         => '',
		'description'  => '',
		'threshold'    => 0.00,
		'productIDs'   => '',
		'freeDelivery' => false
		
	);
	
	

}


?>