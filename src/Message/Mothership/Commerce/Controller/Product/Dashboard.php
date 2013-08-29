<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Dashboard extends Controller
{

	public function index()
	{
		return $this->render('::product:dashboard');
	}
}
