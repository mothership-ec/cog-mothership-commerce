<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Mothership\ControlPanel\Event\Dashboard\DashboardEvent;
use Message\Cog\HTTP\Response;

class Dashboard extends Controller
{
	public function index()
	{
		return $this->render('Message:Mothership:Commerce::product:dashboard');
	}

	public function productTable()
	{
		// skip loading all the products if ajax request
		if ($this->get('request')->server->has('HTTP_X_REQUESTED_WITH') && 
			strtolower($this->get('request')->server->get('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest') {
			return new Response();
		}

		$event = $this->get('event.dispatcher')->dispatch(
			'dashboard.commerce.products',
			new DashboardEvent
		);

		$products = $this->get('product.loader')->getAll();


		return $this->render('Message:Mothership:Commerce::product:product-table', [
			'products' => $products,
			'dashboardReferences' => $event->getReferences()
		]);
	}
}
