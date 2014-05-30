<?php

namespace Message\Mothership\Commerce\Controller\Module\Dashboard;

use Message\Cog\Controller\Controller;

/**
 * Orders activity dashboard module.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class OrdersActivity extends Controller
{
	/**
	 * Get the count of orders placed and dispatched over the past 7 days.
	 *
	 * @return Message\Cog\HTTP\Response
	 */
	public function index()
	{
		$ordersIn  = $this->get('statistics')->get('orders.in');
		$ordersOut = $this->get('statistics')->get('orders.out');

		$in  = (int) $ordersIn->range->getTotal($ordersIn->range->getWeekAgo());
		$out = (int) $ordersOut->range->getTotal($ordersOut->range->getWeekAgo());

		return $this->render('Message:Mothership:ControlPanel::module:dashboard:two-numbers', [
			'label' => 'Orders activity (week)',
			'numbers' => [
				'in'  => [
					'label' => 'In',
					'value' => $in
				],
				'out' => [
					'label' => 'Out',
					'value' => $out
				],
			]
		]);
	}
}