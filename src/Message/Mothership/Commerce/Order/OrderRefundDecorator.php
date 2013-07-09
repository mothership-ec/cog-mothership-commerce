<?php

/**
 * NOTE: This class needs some work before use with
 * Mothership. There are two function calls to functions
 * that do not exist to get WorldPay config data.
 * 
 * The refund functionality should be extracted and work
 * with GlobalCollect/WorldPay/SagePay.
 * */
class OrderRefundDecorator
{
	
	protected $_order;
	protected $_payment;
	protected $_amount;
	protected $_reference;
	protected $_reasonID;

	public function __construct(OrderUpdate $order)
	{
		$this->_order = $order;
	}

	public function setAmount($amount)
	{
		$this->_amount = $amount;
	}

	public function setReasonID($reasonID)
	{
		$this->_reasonID = $reasonID;
	}

	/**
	 * Process the refund
	 * 
	 * @return OrderRefund
	 */
	public function process()
	{
		$this->_validate();
		$this->_setPayment();
		if (!$this->_performRefund()) {
			// IF REFUND FAILED, SET MANUAL REFERENCE
			$this->_reference = 'manual';
		}
		return $this->_addRefund();
	}

	protected function _validate()
	{
		// VALIDATE REFUND AMOUNT
		if (!$this->_amount || $this->_amount > $this->_order->getTotal()) {
			throw new Exception('Amount not set or invalid amount set');
		}
		// VALIDATE PRESENCE OF REFUND REASON
		if (!$this->_reasonID) {
			throw new Exception('Refund reason is not set');
		}
	}

	/**
	 * Sets a suitable payment on the order that can be refunded
	 * NOTE: currently only WorldPay payments suported
	 */
	protected function _setPayment()
	{
		foreach ($this->_order->getPayments() as $payment) {
			// IF PAYMENT WAS MORE OR EQUAL TO REFUND AMOUNT
			// AND PAYMENT TYPE IS WORLDPAY, USE PAYMENT
			if ($payment->amount >= $this->_amount 
				&& in_array($payment->typeID, array(1, 3))) {
				$this->_payment = $payment;
				return true;				
			}
		}
		throw new Exception('Could not find a suitable payment to refund against');
	}

	/**
	 * Performs refund with the appropriate payment provider
	 * REFACTOR: once we have multiple payment providers, this will need some work
	 */
	protected function _performRefund()
	{
		switch ($this->_payment->typeID) {
			case 1:
			case 3:
				
				$vars = array(
					'instId'   => getWorldPayRemoteInstallationID($this->_order->currencyID),
					'authPW'   => getWorldPayRemoteAuthPassword($this->_order->currencyID),
					'cartId'   => 'Refund',
					'op'       => ($this->_amount < $this->_order->getTotal()) ? 'refund-partial' : 'refund-full',
					'transId'  => $this->_payment->reference,
					'amount'   => $this->_amount,
					'currency' => $this->_order->currencyID,
					'refund'   => 'Issue Refund',
				);

				switch(SITE_MODE) {
					case 'live':
						$url = 'https://select.worldpay.com/wcc/itransaction';
						break;
					default:
						$vars['testMode'] = 100;
						$url = 'https://select-test.worldpay.com/wcc/itransaction';
				}
				
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_FAILONERROR, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				$response = curl_exec($ch);
				curl_close($ch);
				
				if (!$response) {
					throw new Exception('Refund failed: WorldPay returned no data');
				}

				$this->_reference = $this->_payment->reference;

				return (array_shift(explode(',', $response)) == 'A') ? true : false;
				
				break;
		}
	}

	/**
	 * Adds refund object to order
	 * 
	 * @return OrderRefund
	 */
	protected function _addRefund()
	{
		// ADD REFUND OBJECT TO ORDER
		$refund = new OrderRefund($this->_amount, $this->_reasonID);
		$refund->reference($this->_reference);
		if($this->_reference != 'manual') {
			$refund->typeID($this->_payment->typeID);
		}
		$refundID = $this->_order->addRefund($refund);
		return $this->_order->getRefunds($refundID);
	}

}