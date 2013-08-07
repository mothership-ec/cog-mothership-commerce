<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\OrderItemStatuses;

class Listing extends Controller
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

		return $this->render('Message:Mothership:Commerce::order:listing:view', array(
			'orders' => $this->_orders,
			'heading' => $heading,
			'search_form' => $this->_getSearchForm(),
		));
	}

	public function shipped()
	{
		// TODO: Load actual shipped orders!
		$this->_orders = $this->get('order.loader')->getByCurrentItemStatus(array(
			OrderItemStatuses::POSTAGED,
		));

		$heading = $this->trans('ms.commerce.order.order.shipped-orders-title');

		return $this->render('Message:Mothership:Commerce::order:listing:view', array(
			'orders' => $this->_orders,
			'heading' => $heading,
			'search_form' => $this->_getSearchForm(),
		));
	}

	public function dashboard()
	{
		return $this->render('::order:listing:dashboard', array(
			'search_form' => $this->_getSearchForm(),
		));
	}

	protected function _getSearchForm()
	{
		$form = $this->get('form')
			->setName('order_search')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cp.file_manager.search.forward'));
		$form->add('term', 'search', 'Enter search term...');

		return $form;
	}
}
