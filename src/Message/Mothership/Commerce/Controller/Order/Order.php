<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Ecommerce\OrderItemStatuses;

class Order extends Controller
{
	protected $_orders;

	public function index($orderID)
	{
		return $this->redirectToRoute('ms.commerce.order.detail.view.order-overview', array('orderID' => $orderID));
	}

	public function allOrders()
	{
		// TODO: Load actual orders!
		$this->_orders = $this->get('order.loader')->getByCurrentItemStatus(array(
			OrderItemStatuses::PRINTED,
			OrderItemStatuses::PICKED,
			OrderItemStatuses::PACKED,
			OrderItemStatuses::POSTAGED,
		));

		return $this->render('Message:Mothership:Commerce::order:all-orders:all-order-view', array(
			'orders' => $this->_orders,
		));
	}
}
