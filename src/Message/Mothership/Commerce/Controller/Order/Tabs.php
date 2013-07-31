<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Tabs extends Controller
{

	public function create($orderId)
	{
		$data = array('orderId' => $orderId);
		$tabs = array(
			'Order Details'	=> $this->generateUrl('ms.commerce.order.view.order-details', $data),
			'Items'   		=> $this->generateUrl('ms.commerce.order.view.items', $data),
			'Addresses'     => $this->generateUrl('ms.commerce.order.view.addresses', $data),
			'Dispatches'    => $this->generateUrl('ms.commerce.order.view.addresses', $data),
			'Notes'      	=> $this->generateUrl('ms.commerce.order.view.addresses', $data),
		);

		$current = ucfirst(trim(strrchr($this->get('http.request.master')->get('_controller'), '::'), ':'));
		return $this->render('Message:Mothership:Commerce::order:tabs', array(
			'tabs'    => $tabs,
			'current' => $current,
		));
	}

}
	