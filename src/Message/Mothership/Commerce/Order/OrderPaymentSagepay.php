<?php


class OrderPaymentSagepay extends OrderPayment {

	protected function setType() {
		$this->typeID = 1;
	}
	
	public function getOrderInfo() {
		//dump($this);
		
		$db = new DBquery;
		
		$sql = 'SELECT txcode,sec_code,payment_reference 
						FROM lkp_order_basket 
						JOIN order_payment USING (order_id) 
						JOIN lkp_order_sec ON (lkp_order_sec.txcode = lkp_order_basket.basket_id)  
						WHERE order_payment.payment_id = '.$this->paymentID;
		$db->query($sql);
		if($row = $db->row()) {
		   return (object) $row;
		}
	}

}


?>