<?php

namespace Message\Mothership\Commerce\Gateway;

use Message\User\UserInterface;

class Sagepay extends Wrapper
{

	public function __construct($query, UserInterface $user)
	{
		$this->_user  = $user;
		$this->_query = $query;
		$this->setGateway('Sagepay_Server');
	}

	public function send()
	{
		$this->_request = $this->_gateway->purchase(array(
			'amount'        => $this->_paymentAmount,
			'card'          => $this->_card,
			'currency'      => $this->_currencyID,
			'returnUrl'     => $this->_redirect,
			'transactionId' => $this->_order->user->id.time(),
			'description'   => 'Uniform Wares payment',
		));

		$this->_response = $this->_request->send();

		return $this->_response;
	}
}