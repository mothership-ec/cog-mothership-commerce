<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;

class Dashboard extends Controller
{

	public function index()
	{

		return $this->render('::order:dashboard');
	}
}
