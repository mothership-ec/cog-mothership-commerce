<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Mothership\Ecommerce\OrderItemStatuses;
use Message\Mothership\Commerce\Order\Statuses;
use Message\Mothership\Commerce\Order\Events;
use Message\Mothership\ControlPanel\Event\BuildMenuEvent;

use Message\Mothership\Commerce\Product\Stock\Location\Location;


class Listing extends Controller
{
	protected $_orders;

	public function all()
	{
		// TODO: Load actual orders!
		$this->_orders = $this->get('order.loader')->getByStatus(array(
			Statuses::AWAITING_DISPATCH,
			Statuses::PROCESSING,
			Statuses::PARTIALLY_DISPATCHED,
			Statuses::PARTIALLY_RECEIVED,
			Statuses::DISPATCHED,
			Statuses::RECEIVED,
		), 50); // TEMP: limit to 50

		$heading = $this->trans('ms.commerce.order.order.all-orders-title');

		return $this->render('Message:Mothership:Commerce::order:listing:order-listing', array(
			'orders' => $this->_orders,
			'heading' => $heading,
		));
	}

	public function shipped()
	{
		// TODO: Load actual shipped orders!
		$this->_orders = $this->get('order.loader')->getByStatus(array(
			Statuses::DISPATCHED,
		), 50); // TEMP: limit to 50

		$heading = $this->trans('ms.commerce.order.order.shipped-orders-title');

		return $this->render('Message:Mothership:Commerce::order:listing:order-listing', array(
			'orders' => $this->_orders,
			'heading' => $heading,
		));
	}

	public function searchAction()
	{
		$form = $this->createForm($this->get('commerce.form.order.simple_search'), null, [
			'action' => $this->generateUrl('ms.commerce.order.search.action'),
		]);

		$form->handleRequest();

		if ($form->isValid()) {
			$term = $form->get('term')->getData();

			$order = $this->get('order.loader')->getById($term);

			if ($order) {
				return $this->redirectToRoute('ms.commerce.order.detail.view', array('orderID' => $order->id));
			}

			// If search did not match an ID instead look for a tracking code match.
			$orders = $this->get('order.loader')->getByTrackingCode($term);

			if (count($orders)) {
				return $this->render('Message:Mothership:Commerce::order:listing:order-listing', array(
					'orders' => $orders,
					'heading' => sprintf('Orders matching tracking code "%s".', $term),
				));
			}

		}
		// If there were no matches return the error
		$this->addFlash('warning', sprintf('No search results were found for "%s".', $term));
		return $this->redirectToReferer();
	}

	/**
	 * Render the sidebar.
	 *
	 * This fires the event defined as `Events::BUILD_ORDER_SIDEBAR` of type
	 * `BuildMenuEvent`. This event allows listeners to add items to order's
	 * sidebar.
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function sidebar()
	{
		$form = $this->createForm($this->get('commerce.form.order.simple_search'), null, [
			'action' => $this->generateUrl('ms.commerce.order.search.action'),
		]);
		$event = new BuildMenuEvent;

		$this->get('event.dispatcher')->dispatch(
			Events::BUILD_ORDER_SIDEBAR,
			$event
		);

		$event->setClassOnCurrent($this->get('http.request.master'), 'current');

		return $this->render('Message:Mothership:Commerce::order:listing:sidebar', array(
			'search_form' => $form,
			'items' => $event->getItems(),
		));
	}

	public function dashboard()
	{
		return $this->render('::order:listing:dashboard', array(
		));
	}

	protected function _getSearchForm()
	{
		$form = $this->get('form')
			->setName('order_search')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.commerce.order.search.action'));
		$form->add('term', 'search', 'Search');

		return $form;
	}
}
