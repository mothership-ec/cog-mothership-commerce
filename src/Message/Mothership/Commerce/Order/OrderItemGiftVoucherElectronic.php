<?php

class OrderItemGiftVoucherElectronic extends OrderItemGiftVoucher
{
	
	public function send($orderID)
	{
		$order = new Order($orderID);
		
		foreach($order->getItems() as $item) {
			
			if($item->itemID == $this->itemID) {
				$this->unitID = $item->unitID;
				$this->note = $item->note;
			}
			
		}
		
		if(!$this->unitID) {
			return false;
		}
		
		$this->loadVoucher();
		
		$email = new MultipartEmail('evoucher');
		
		$email->recipient = $this->giftVoucher->recipientEmail;
		$email->subject = $order->userName.' sent you a ' . Config::get('merchant')->name . ' Gift voucher';
		
		$email->replaceTokens(array('RECIPIENT_NAME'	=> $this->giftVoucher->recipientName,
									'CURRENCY'			=> $order->currencySymbol,
									'ENCODED_CURRENCY'	=> htmlentities($order->currencySymbol, ENT_NOQUOTES, 'UTF-8'),
									'AMOUNT'			=> $this->giftVoucher->getPrice($order->getComplexCurrencyID()),
									'MESSAGE'			=> $this->giftVoucher->recipientMessage,
									'MESSAGE_HTML'		=> nl2br($this->giftVoucher->recipientMessage),
									'CODE'				=> $this->note));
		
		return $email->send();
	}
	
}