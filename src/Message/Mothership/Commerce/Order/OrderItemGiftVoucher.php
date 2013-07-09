<?php

class OrderItemGiftVoucher extends OrderItem
{
	
	protected $giftVoucher;
	
	public function addVoucher(GiftVoucher $voucher)
	{
		$this->giftVoucher = $voucher;
	}
	
	public function commit()
	{
		$this->note = $this->giftVoucher->commit();
	}
		
}