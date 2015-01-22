<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Mothership\ControlPanel\Event\Dashboard\DashboardEvent;

class Dashboard extends Controller
{
	public function index()
	{
		return $this->render('Message:Mothership:Commerce::product:dashboard');
	}

	public function productTable()
	{
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
