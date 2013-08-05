<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Order extends Controller
{
	protected $_order;

	public function index($orderId)
	{
		return $this->redirectToRoute('ms.commerce.order.detail.view.order-summary', array('orderId' => $orderId));
	}

	public function orderSummary($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);

		return $this->render('Message:Mothership:Commerce::order:detail:order-summary:order-summary', array(
			'order' => $this->_order,
		));
	}

	public function orderDetail($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);

		return $this->render('Message:Mothership:Commerce::order:detail:order-summary:detail', array(
			'order' => $this->_order,
		));
	}

	public function sidebar($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);

		return $this->render('Message:Mothership:Commerce::order:detail:sidebar', array(
			'order' => $this->_order,
		));
	}
}
