<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Order extends Controller
{
	protected $_order;

	public function index($orderId)
	{
		return $this->redirectToRoute('ms.commerce.order.view.order-details', array('orderId' => $orderId));
	}

	public function orderDetails($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);

		return $this->render('::order:order-details:order-details', array(
			'order' => $this->_order,
		));
	}

	public function sidebar($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);

		return $this->render('Message:Mothership:Commerce::order:sidebar', array(
			'order' => $this->_order,
		));
	}
}
