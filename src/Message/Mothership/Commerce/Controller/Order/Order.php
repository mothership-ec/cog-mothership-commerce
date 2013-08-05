<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Ecommerce\OrderItemStatuses;

class Order extends Controller
{
	protected $_order;

	public function index($orderID)
	{
		return $this->redirectToRoute('ms.commerce.order.detail.view.order-overview', array('orderID' => $orderID));
	}

	public function allOrders()
	{
		$orders = $this->get('order.loader')->getByCurrentItemStatus(array(
			OrderItemStatuses::PRINTED,
			OrderItemStatuses::PICKED,
			OrderItemStatuses::PACKED,
			OrderItemStatuses::POSTAGED,
		));

		return $this->render('Message:Mothership:Commerce::order:all-orders:all-order-view', array(
			'orders' => $orders,
		));
	}

	public function orderOverview($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);

		return $this->render('Message:Mothership:Commerce::order:detail:order-overview:order-overview', array(
			'order' => $this->_order,
		));
	}

	public function orderDetail($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);

		return $this->render('Message:Mothership:Commerce::order:detail:order-overview:detail', array(
			'order' => $this->_order,
		));
	}

	public function sidebar($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);

		return $this->render('Message:Mothership:Commerce::order:detail:sidebar', array(
			'order' => $this->_order,
		));
	}
}
