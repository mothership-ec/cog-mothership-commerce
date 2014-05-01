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
		$in  = (int) $this->get('statistics')->get('orders.in.weekly')->getCounter();
		$out = (int) $this->get('statistics')->get('orders.out.weekly')->getCounter();

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