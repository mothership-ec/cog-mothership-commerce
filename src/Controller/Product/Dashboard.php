<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\ControlPanel\Event\Dashboard\DashboardEvent;

class Dashboard extends Controller
{
	public function index()
	{
		$event = $this->get('event.dispatcher')->dispatch(
			'dashboard.commerce.products',
			new DashboardEvent
		);

		return $this->render('::product:dashboard', [
			'dashboardReferences' => $event->getReferences()
		]);
	}
}
