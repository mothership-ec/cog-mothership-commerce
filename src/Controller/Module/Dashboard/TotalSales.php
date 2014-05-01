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
		$dataset  = $this->get('statistics')->get('sales.net.daily');
		$net      = $dataset->getRange($dataset::WEEK);
		$totalNet = $dataset->getTotal($dataset::WEEK);

		return $this->render('Message:Mothership:ControlPanel::module:dashboard:area-graph', [
			'label'   => 'Total sales (week)',
			'rows'  => $net,
			'numbers' => [
				[
					'value'   => $totalNet,
					'filters' => 'price',
				]
			]
		]);
	}
}