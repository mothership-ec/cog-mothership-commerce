<?php

namespace Message\Mothership\Commerce\Controller\Module;

use Message\Cog\Controller\Controller;

class Basket extends Controller
{
	public function display()
	{
		$order          = $this->get('basket')->getOrder();
		$totalListPrice = 0;
		$totalDiscount  = 0;

		foreach ($order->items as $item) {
			$totalListPrice += $item->listPrice;
			$totalDiscount  += $item->discount;
		}

		return $this->render('Message:Mothership:Commerce::basket', array(
			'order'          => $order,
			'totalListPrice' => $totalListPrice - $totalDiscount,
		));
	}
}