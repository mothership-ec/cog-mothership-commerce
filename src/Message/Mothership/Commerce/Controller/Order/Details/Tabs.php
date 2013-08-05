<?php

namespace Message\Mothership\Commerce\Controller\Order\Details;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Tabs extends Controller
{

	public function create($orderId)
	{
		$data = array('orderId' => $orderId);
		$tabs = array(
			$this->trans('ms.commerce.order.order.order-overview.title')	=> $this->generateUrl('ms.commerce.order.details.view.order-summary', 	$data),
			$this->trans('ms.commerce.order.item.items.title')   			=> $this->generateUrl('ms.commerce.order.details.view.items', 			$data),
			$this->trans('ms.commerce.order.address.addresses.title')   	=> $this->generateUrl('ms.commerce.order.details.view.addresses', 		$data),
			$this->trans('ms.commerce.order.payment.payments.title')   		=> $this->generateUrl('ms.commerce.order.details.view.payments', 		$data),
			$this->trans('ms.commerce.order.dispatch.dispatches.title')    	=> $this->generateUrl('ms.commerce.order.details.view.dispatches', 		$data),
			$this->trans('ms.commerce.order.note.notes.title') 				=> $this->generateUrl('ms.commerce.order.details.view.notes',	 		$data),
		);

		$current = ucfirst(trim(strrchr($this->get('http.request.master')->get('_controller'), '::'), ':'));
		return $this->render('Message:Mothership:Commerce::order:details:tabs', array(
			'tabs'    => $tabs,
			'current' => $current,
		));
	}

}
	