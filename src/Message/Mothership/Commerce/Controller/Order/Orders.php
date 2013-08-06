<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\OrderItemStatuses;

class Orders extends Controller
{
	protected $_orders;

	public function index()
	{
		return $this->redirectToRoute('ms.commerce.order.view.all');
	}

	public function all()
	{
		// TODO: Load actual orders!
		$this->_orders = $this->get('order.loader')->getByCurrentItemStatus(array(
			OrderItemStatuses::PRINTED,
			OrderItemStatuses::PICKED,
			OrderItemStatuses::PACKED,
			OrderItemStatuses::POSTAGED,
		));

		$heading = $this->trans('ms.commerce.order.order.all-orders-title');

		return $this->render('Message:Mothership:Commerce::order:orders-view', array(
			'orders' => $this->_orders,
			'heading' => $heading,
		));
	}

	public function shipped()
	{
		// TODO: Load actual shipped orders!
		$this->_orders = $this->get('order.loader')->getByCurrentItemStatus(array(
			OrderItemStatuses::POSTAGED,
		));

		$heading = $this->trans('ms.commerce.order.order.shipped-orders-title');

		return $this->render('Message:Mothership:Commerce::order:orders-view', array(
			'orders' => $this->_orders,
			'heading' => $heading,
		));
	}
}
