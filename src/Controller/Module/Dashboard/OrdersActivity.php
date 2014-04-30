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
		$in  = $this->get('stats')->getValue('orders.in.weekly');
		$out = $this->get('stats')->getValue('orders.out.weekly');

		return $this->render('Message:Mothership:ControlPanel::module:dashboard:big-numbers', [
			'label' => 'Orders activity (week)',
			'numbers' => [
				'in'  => [
					'label' => 'In'
					'value' => $in,
				],
				'out' => [
					'label' => 'Out',
					'value' => $out,
				],
			]
		]);
	}
}