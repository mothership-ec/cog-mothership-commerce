<?php

class OrderDespatchElectronic extends OrderDespatch
{

	protected function setType()
	{
		$this->typeID = 7;
	}
	
	public function getTrackingLink()
	{
		return;
	}
	
	public function send()
	{
		foreach($this->itemIDs as $itemID) {
			$item = new OrderItemGiftVoucherElectronic($itemID);
			$item->send($this->orderID);
		}
	}

}