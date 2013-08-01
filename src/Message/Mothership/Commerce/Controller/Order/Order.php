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

	public function sidebar()
	{
		$menu = array(
			'order-details' => $this->trans('ms.commerce.order.order.order-details.title'),
			'item-summary' => $this->trans('ms.commerce.order.item.item-summary.title'),
			'address-summary' => $this->trans('ms.commerce.order.address.address-summary.title'),
		);

		return $this->render('Message:Mothership:Commerce::order:order-details:sidebar', array(
			'menu' => $menu,
		));
	}
}
