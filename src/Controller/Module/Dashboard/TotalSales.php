<?php

namespace Message\Mothership\Commerce\Controller\Module\Dashboard;

use Message\Cog\Controller\Controller;

/**
 * Total sales dashboard module
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class TotalSales extends Controller
{
	/**
	 * Get the daily net sales for the past 7 days.
	 *
	 * @return Message\Cog\HTTP\Response
	 */
	public function index()
	{
		// $stats->addDataset('sales.net.daily', $stats::VALUE, $stats::DAILY);
		// $stats->addDataset('sales.gross.daily', $stats::VALUE, $stats::DAILY);

		$net = $this->get('stats')->getRange('sales.net.daily', strtotime('7 days ago'));

		return $this->render('Message:Mothership:ControlPanel::module:dashboard:area-graph', [
			'label'   => 'Total sales (week)',
			'values'  => $net,
			'numbers' => [
				[
					'label'   => false,
					'value'   => $totalNet,
					'filters' => 'price',
				]
			]
		]);
	}
}