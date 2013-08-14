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
		));

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
		));

		$heading = $this->trans('ms.commerce.order.order.shipped-orders-title');

		return $this->render('Message:Mothership:Commerce::order:listing:order-listing', array(
			'orders' => $this->_orders,
			'heading' => $heading,
		));
	}

	public function searchAction()
	{
		$form = $this->_getSearchForm();
		if ($form->isValid() && $data = $form->getFilteredData()) {
			$orderID = $data['term'];
			
			$order = $this->get('order.loader')->getById($orderID);

			if ($order) {
				return $this->redirectToRoute('ms.commerce.order.detail.view.index', array('orderID' => $order->id));
			} else {
				$this->addFlash('warning', sprintf('No search results were found for "%s"', $orderID));
				return $this->redirectToReferer();
			}
		}
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
		$event = new BuildMenuEvent;

		$this->get('event.dispatcher')->dispatch(
			Events::BUILD_ORDER_SIDEBAR,
			$event
		);

		$event->setClassOnCurrent($this->get('http.request.master'), 'current');

		return $this->render('Message:Mothership:Commerce::order:listing:sidebar', array(
			'search_form' => $this->_getSearchForm(),
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
