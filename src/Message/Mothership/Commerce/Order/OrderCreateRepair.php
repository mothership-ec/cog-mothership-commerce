<?php


class OrderCreateRepair extends OrderCreate
{
	public function addRepair(Repair $repair)
	{
		$orderRepair = new OrderRepair;
		$orderRepair->orderItemID  = $repair->getOrderItemID();
		$orderRepair->catalogueID  = $repair->getCatalogueID();
		$orderRepair->notes        = $repair->getNotes();
		$orderRepair->purchaseDate = $repair->getPurchaseDate();
		$orderRepair->retailer     = $repair->getRetailer();
		$orderRepair->faulty       = (int)$repair->isFaulty();

		$available = $repair->getRepairOptions(false);
		$selected  = $repair->getOptions();

		foreach($selected as $id => $price) {
			$orderRepair->addOption((object)array(
				'serviceID' => $id, 
				'price'		=> $price,
				'name'		=> $available[$id]['service_option_name'],
				'description' => '',
			));
		}

		$this->repairs->add($orderRepair);
	}
}


