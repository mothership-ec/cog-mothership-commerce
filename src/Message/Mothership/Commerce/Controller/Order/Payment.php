<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Payment extends Controller
{
	protected $_order;
	protected $_payments;

	public function payments($orderID)
	{
		return $this->_loadOrderAndPaymentsAndRender($orderID, 'Message:Mothership:Commerce::order:detail:payment:payments');
	}

	protected function _loadOrderAndPaymentsAndRender($orderID, $view)
	{
		$this->_loadOrderAndPayments($orderID);

		return $this->render($view, array(
			'payments' => $this->_payments,
			'order' => $this->_order,
		));
	}

	protected function _loadOrderAndPayments($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_payments = $this->get('order.payment.loader')->getByOrder($this->_order);
	}

}
