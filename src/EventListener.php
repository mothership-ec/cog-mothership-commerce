<?php

namespace Message\Mothership\Commerce;

use Message\Cog\HTTP\RedirectResponse;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;

use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Events as OrderEvents;

use Message\Mothership\ControlPanel\Event\BuildMenuEvent;
use Message\Mothership\ControlPanel\Event\Dashboard\Activity;
use Message\Mothership\ControlPanel\Event\Dashboard\DashboardEvent;
use Message\Mothership\ControlPanel\Event\Dashboard\ActivitySummaryEvent;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Message\Mothership\Report\Event as ReportEvents;

/**
 * Event listener for core Mothership Commerce functionality.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			BuildMenuEvent::BUILD_MAIN_MENU => array(
				array('registerMainMenuItems'),
			),
			OrderEvents::BUILD_ORDER_SIDEBAR => array(
				array('registerSidebarItems'),
			),
			OrderEvents::BUILD_ORDER_TABS => array(
				array('registerTabItems'),
			),
			OrderEvents::CREATE_COMPLETE => array(
				array('recordOrderIn'),
				array('recordSalesNet'),
				array('recordProductsSales'),
			),
			OrderEvents::DISPATCH_SHIPPED => array(
				array('recordOrderOut'),
			),
			// OrderEvents::DELETE_END => array(
			// 	array('recordOrderDeleted'),
			// 	array('recordSalesNetDeleted'),
			// 	array('recordProductsSalesDeleted'),
			// ),
			DashboardEvent::DASHBOARD_INDEX => array(
				array('buildDashboardProducts'),
				array('buildDashboardOrders'),
			),
			'dashboard.commerce.products' => array(
				'buildDashboardProducts',
			),
			'dashboard.commerce.orders' => array(
				'buildDashboardOrders',
			),
			ActivitySummaryEvent::DASHBOARD_ACTIVITY_SUMMARY => array(
				'buildDashboardBlockUserSummary',
			),
			ReportEvents\ReportEvent::REGISTER_REPORTS => [
				'registerReports'
			],
		);
	}

	/**
	 * Register items to the main menu of the control panel.
	 *
	 * @param BuildMenuEvent $event The event
	 */
	public function registerMainMenuItems(BuildMenuEvent $event)
	{
		$event->addItem('ms.commerce.product.dashboard', 'Products', array('ms.product'));
		$event->addItem('ms.commerce.order.view.dashboard', 'Orders', array('ms.order'));
	}

	/**
	 * Register items to the sidebar of the orders-pages.
	 *
	 * @param BuildMenuEvent $event The event
	 */
	public function registerSidebarItems(BuildMenuEvent $event)
	{
		$event->addItem('ms.commerce.order.view.all', 'All Orders');
		$event->addItem('ms.commerce.order.view.shipped', 'Shipped Orders');
	}

	/**
	 * Register items to the sidebar of the orders-pages.
	 *
	 * @param BuildMenuEvent $event The event
	 */
	public function registerTabItems(Event\BuildOrderTabsEvent $event)
	{
		$event->addItem('ms.commerce.order.detail.view', 			'ms.commerce.order.order.overview-title');
		$event->addItem('ms.commerce.order.detail.view.items', 		'ms.commerce.order.item.listing-title');
		$event->addItem('ms.commerce.order.detail.view.addresses', 	'ms.commerce.order.address.listing-title');
		$event->addItem('ms.commerce.order.detail.view.payments', 	'ms.commerce.order.payment.listing-title');
		$event->addItem('ms.commerce.order.detail.view.dispatches', 'ms.commerce.order.dispatch.listing-title');
		$event->addItem('ms.commerce.order.detail.view.notes', 		'ms.commerce.order.note.listing-title');
		$event->addItem('ms.commerce.order.detail.view.documents', 	'ms.commerce.order.document.listing-title');
	}

	/**
	 * Add controller references to the products dashboard.
	 *
	 * @param  DashboardEvent $event
	 */
	public function buildDashboardProducts(DashboardEvent $event)
	{
		$event->addReference('Message:Mothership:Commerce::Controller:Module:Dashboard:PopularProducts#index');
	}

	/**
	 * Add controller references to the orders dashboard.
	 *
	 * @param  DashboardEvent $event
	 */
	public function buildDashboardOrders(DashboardEvent $event)
	{
		$event->addReference('Message:Mothership:Commerce::Controller:Module:Dashboard:OrdersActivity#index');
		$event->addReference('Message:Mothership:Commerce::Controller:Module:Dashboard:TotalSales#index');
	}

	/**
	 * Add the user's last edited product and order into the user summary
	 * dashboard block.
	 *
	 * @param  ActivitySummaryEvent $event
	 */
	public function buildDashboardBlockUserSummary(ActivitySummaryEvent $event)
	{
		$productID = $this->get('db.query')->run("
			SELECT product_id
			FROM product
			WHERE :userID?b IS NULL OR updated_by = :userID?i
			ORDER BY updated_at DESC
			LIMIT 1
		", [
			'userID' => $event->getUser()->id
		]);

		if (count($productID)) {
			$product = $this->get('product.loader')->getByID($productID[0]->product_id);

			if ($product) {
				$url = $this->get('routing.generator')->generate(
					'ms.commerce.product.edit.attributes',
					['productID' => $product->id],
					UrlGeneratorInterface::ABSOLUTE_PATH
				);

				$event->addActivity(new Activity(
					'Last edited product',
					$product->authorship->updatedAt(),
					$product->name,
					$url
				));
			}
		}

		$orderID = $this->get('db.query')->run("
			SELECT order_id
			FROM order_summary
			WHERE :userID?b IS NULL OR updated_by = :userID?i
			ORDER BY updated_at DESC
			LIMIT 1
		", [
			'userID' => $event->getUser()->id
		]);

		if (count($orderID)) {
			$order = $this->get('order.loader')->getByID($orderID[0]->order_id);

			if ($order) {
				$url = $this->get('routing.generator')->generate(
					'ms.commerce.order.detail.view',
					['orderID' => $order->id],
					UrlGeneratorInterface::ABSOLUTE_PATH
				);

				$event->addActivity(new Activity(
					'Last edited order',
					$order->authorship->updatedAt(),
					'#' . $order->id,
					$url
				));
			}
		}
	}

	/**
	 * Increment the orders.in stat.
	 *
	 * @param  Event\Event $event
	 */
	public function recordOrderIn(Event\Event $event)
	{
		$this->get('statistics')->get('orders.in')->counter->increment();
	}

	/**
	 * Increment the orders.out stat.
	 *
	 * @param  Event\Event $event
	 */
	public function recordOrderOut(Event\Event $event)
	{
		$this->get('statistics')->get('orders.out')->counter->increment();
	}

	/**
	 * Decrement the orders.in stat.
	 *
	 * @param  EventEvent $event
	 */
	public function recordOrderDeleted(Event\Event $event)
	{
		$this->get('statistics')->get('orders.in')->counter->decrement();
	}

	/**
	 * Increment the sales.net stat with the orders total net.
	 *
	 * @param  Event\Event $event
	 */
	public function recordSalesNet(Event\Event $event)
	{
		$this->get('statistics')->get('sales.net')
			->counter->increment($event->getOrder()->totalNet);
	}

	/**
	 * Decrement the sales.net stat with the orders total net.
	 *
	 * @param  Event\Event $event
	 */
	public function recordSalesNetDeleted(Event\Event $event)
	{
		$this->get('statistics')->get('sales.net')
			->counter->decrement($event->getOrder()->totalNet);
	}

	/**
	 * Increment the products.sales stat for each product ordered.
	 *
	 * @param  Event\Event $event
	 */
	public function recordProductsSales(Event\Event $event)
	{
		$dataset = $this->get('statistics')->get('products.sales');
		foreach ($event->getOrder()->getItemRows() as $unitID => $items) {
			$dataset->counter->increment($unitID, count($items));
		}
	}

	/**
	 * Decrement the products.sales stat for each product ordered.
	 *
	 * @param  Event\Event $event
	 */
	public function recordProductsSalesDeleted(Event\Event $event)
	{
		$dataset = $this->get('statistics')->get('products.sales');
		foreach ($event->getOrder()->getItemRows() as $unitID => $items) {
			$dataset->counter->decrement($unitID, count($items));
		}
	}

	public function registerReports(ReportEvents\BuildReportCollectionEvent $event)
	{
		foreach ($this->get('commerce.reports') as $report) {
			$event->registerReport($report);
		}
	}
}