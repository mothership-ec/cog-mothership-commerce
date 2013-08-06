<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;

class OrderDetail extends Controller
{
	protected $_order;
	protected $_addresses;
	protected $_items;
	protected $_metadata;
	protected $_payments;
	protected $_dispatches;
	protected $_notes;

	public function orderOverview($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_addresses = $this->get('order.address.loader')->getByOrder($this->_order);
		$this->_items = $this->get('order.item.loader')->getByOrder($this->_order);
		$this->_metadata = $this->_order->metadata;

		return $this->render('::order:order:overview', array(
			"order" => $this->_order,
			"addresses" => $this->_addresses,
			"items" => $this->_items,
			"metadata" => $this->_metadata,
		));
	}

	public function addressListing($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_addresses = $this->get('order.address.loader')->getByOrder($this->_order);
		return $this->render('::order:address:listing', array(
			'order' => $this->_order,
			'addresses' => $this->_addresses,
		));
	}

	public function itemListing($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_items = $this->get('order.item.loader')->getByOrder($this->_order);

		$statuses = array();
		foreach($this->_items AS $item) {
			$statuses[$item->id] = $this->get('order.item.status.loader')->getHistory($item);
		}

		return $this->render('::order:item:listing', array(
			'order' => $this->_order,
			'items' => $this->_items,
			'statuses' => $statuses,
		));
	}

	public function paymentListing($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_payments = $this->get('order.payment.loader')->getByOrder($this->_order);
		return $this->render('::order:payment:listing', array(
			'order' => $this->_order,
			'payments' => $this->_payments,
		));
	}

	public function dispatchListing($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_dispatches = $this->get('order.dispatch.loader')->getByOrder($this->_order);
		return $this->render('::order:dispatch:listing', array(
			'order' => $this->_order,
			'dispatches' => $this->_dispatches,
		));
	}

	public function noteListing($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_notes = $this->get('order.note.loader')->getByOrder($this->_order);
		return $this->render('::order:note:listing', array(
			'order' => $this->_order,
			'notes' => $this->_notes,
		));
	}

	public function sidebar($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);

		return $this->render('Message:Mothership:Commerce::order:order-detail:sidebar', array(
			'order' => $this->_order,
		));
	}

	public function tabs($orderID)
	{
		$data = array('orderID' => $orderID);
		$tabs = array(
			$this->trans('ms.commerce.order.order.overview-title')		=> 	$this->generateUrl('ms.commerce.order.detail.view.index', 		$data),
			$this->trans('ms.commerce.order.item.listing-title')		=>	$this->generateUrl('ms.commerce.order.detail.view.items', 		$data),
			$this->trans('ms.commerce.order.address.listing-title')		=>	$this->generateUrl('ms.commerce.order.detail.view.addresses', 	$data),
			$this->trans('ms.commerce.order.payment.listing-title')   	=>	$this->generateUrl('ms.commerce.order.detail.view.payments', 	$data),
			$this->trans('ms.commerce.order.dispatch.listing-title')	=>	$this->generateUrl('ms.commerce.order.detail.view.dispatches', 	$data),
			$this->trans('ms.commerce.order.note.listing-title') 		=>	$this->generateUrl('ms.commerce.order.detail.view.notes',	 	$data),
		);

		$current = ucfirst(trim(strrchr($this->get('http.request.master')->get('_controller'), '::'), ':'));
		return $this->render('Message:Mothership:Commerce::order:order-detail:tabs', array(
			'tabs'    => $tabs,
			'current' => $current,
		));
	}
}
