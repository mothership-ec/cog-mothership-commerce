<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;

use Message\Mothership\Commerce\Order\Events;
use Message\Mothership\Commerce\Order\Statuses;
use Message\Mothership\Commerce\Product\Stock\Location\Location;

use Message\Mothership\Ecommerce\OrderItemStatuses;

use Message\Mothership\ControlPanel\Event\BuildMenuEvent;
use Message\Mothership\ControlPanel\Event\Dashboard\DashboardEvent;


class Listing extends Controller
{
	const DEFAULT_PAGINATION_COUNT = 25;

	protected $_orders;

	public function all()
	{
		$pagination = $this->_getPaginator();

		$heading = $this->trans('ms.commerce.order.order.all-orders-title');

		return $this->render('Message:Mothership:Commerce::order:listing:order-listing', [
			'orders' => $pagination->getCurrentPageResults(),
			'pagination' => $pagination,
			'heading' => $heading,
		]);
	}

	public function shipped()
	{
		$pagination = $this->_getPaginator();
		$pagination->getAdapter()->setStatuses([ Statuses::DISPATCHED ]);

		$heading = $this->trans('ms.commerce.order.order.shipped-orders-title');

		return $this->render('Message:Mothership:Commerce::order:listing:order-listing', [
			'orders' => $pagination->getCurrentPageResults(),
			'pagination' => $pagination,
			'heading' => $heading,
		]);
	}

	public function searchAction()
	{
		$form = $this->createForm($this->get('commerce.form.order.simple_search'), null, [
			'action' => $this->generateUrl('ms.commerce.order.search.action'),
		]);

		$form->handleRequest();

		if ($form->isValid()) {
			$term = $form->get('term')->getData();

			$orders = $this->get('order.loader')->getBySearchTerm($term);

			if (count($orders) === 1) {
				$order = array_shift($orders);
				$this->addFlash('success', $this->trans('ms.commerce.order.order.search.one-result', [
					'%term%' => $term,
				]));

				return $this->redirectToRoute('ms.commerce.order.detail.view', ['orderID' => $order->id]);
			}

			if (count($orders)) {
				return $this->render('Message:Mothership:Commerce::order:listing:order-listing', array(
					'orders' => $orders,
					'heading' => $this->trans('ms.commerce.order.order.search.results', [
						'%amount%' => count($orders),
						'%term%'   => $term
					]),
				));
			}

			// If there were no matches return the error
			$this->addFlash('warning', $this->trans('ms.commerce.order.order.search.no-results', [
				'%term%' => $term,
			]));
		}

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
		$event = $this->get('event.dispatcher')->dispatch(
			'dashboard.commerce.orders',
			new DashboardEvent
		);

		return $this->render('::order:listing:dashboard', [
			'dashboardReferences' => $event->getReferences()
		]);
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

	private function _getPaginator()
	{
		$page = (int) $this->get('request')->get('list-page');
		return $this->get('pagination')
			->setAdapter($this->get('order.pagination.adapter'))
			->setMaxPerPage(self::DEFAULT_PAGINATION_COUNT)
			->setCurrentPage($page)
		;
	}
}

