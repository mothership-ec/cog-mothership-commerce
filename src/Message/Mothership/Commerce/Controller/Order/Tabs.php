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
			$this->trans('ms.commerce.order.order.order-details.title')	=> $this->generateUrl('ms.commerce.order.view.order-details', $data),
			$this->trans('ms.commerce.order.item.items.title')   		=> $this->generateUrl('ms.commerce.order.view.items', $data),
			$this->trans('ms.commerce.order.address.addresses.title')   => $this->generateUrl('ms.commerce.order.view.addresses', $data),
			$this->trans('ms.commerce.order.dispatch.title')    		=> $this->generateUrl('ms.commerce.order.view.addresses', $data),
			$this->trans('ms.commerce.order.note.notes.title') 			=> $this->generateUrl('ms.commerce.order.view.addresses', $data),
		);

		$current = ucfirst(trim(strrchr($this->get('http.request.master')->get('_controller'), '::'), ':'));
		return $this->render('Message:Mothership:Commerce::order:tabs', array(
			'tabs'    => $tabs,
			'current' => $current,
		));
	}

}
	