<?php

class OrderDiscountCampaign extends OrderDiscount
{

	protected $campaign;

	public function setCampaign(Campaign $campaign)
	{
		$this->campaign = $campaign;
	}

	public function setOrderItemDiscounts(Order &$order)
	{
		// DISCOUNT APPLIES TO PARTICULAR PRODUCTS - FIND THE DISCOUNT FOR EACH UNIT AND SAVE
		if($this->campaign->getQualifyMode() != Campaign::QUALIFY_ORDER) {
			
			$unitBenefit = $this->campaign->getProductBenefit();
			$unitDiscounts = array();

			//FIND DISCOUNT FOR EACH UNIT IN ORDER
			foreach($order->getItemArray(null, true) as $items) {
				if(isset($unitBenefit[$items->unitID])) {
					/**
					 * We don't need to worry about off-by-1 errors here, 
					 * as the $productDiscount will always be the benefit * quantity, 
					 * we're just turning it back to that original value
					 **/
					$productDiscount = $unitBenefit[$items->unitID];
					$unitDiscounts[$items->unitID] = $unitBenefit[$items->unitID] / $items->quantity;
				}
			}

			//APPLY DISCOUNT TO EACH APPLICABLE ITEM
			foreach($order->getItems() as $item) {
				if(isset($unitDiscounts[$item->unitID])) {
					$item->discount($unitDiscounts[$item->unitID]);
				}
			}

		}
		else {
			return parent::setOrderItemDiscounts($order);
		}

		return $order;
	}

	protected function setType()
	{
		$this->typeID = 2;
	}

}