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
		// $stats->addDataset('orders.in', $stats::VALUE, $stats::WEEKLY);
		// $stats->addDataset('orders.out', $stats::VALUE, $stats::WEEKLY);

		$in  = $this->get('stats')->getValue('orders.in');
		$out = $this->get('stats')->getValue('orders.out');

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