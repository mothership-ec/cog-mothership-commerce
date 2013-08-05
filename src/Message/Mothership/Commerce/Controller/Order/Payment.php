<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Payment extends Controller
{
	protected $_order;
	protected $_payments;

	public function payments($orderId)
	{
		return $this->_loadOrderAndPaymentsAndRender($orderId, 'Message:Mothership:Commerce::order:detail:payment:payments');
	}

	protected function _loadOrderAndPaymentsAndRender($orderId, $view)
	{
		$this->_loadOrderAndPayments($orderId);

		return $this->render($view, array(
			'payments' => $this->_payments,
			'order' => $this->_order,
		));
	}

	protected function _loadOrderAndPayments($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);
		$this->_payments = $this->get('order.payment.loader')->getByOrder($this->_order);
	}

}
