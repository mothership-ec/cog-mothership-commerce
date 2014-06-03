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
		$dataset  = $this->get('statistics')->get('sales.net');
		$net      = $dataset->range->getValues($dataset->range->getWeekAgo());
		$totalNet = $dataset->range->getTotal($dataset->range->getWeekAgo());

		$rows = [];

		// Pre-fill rows with a total of 0 on each day before now to ensure we
		// don't lose days where there is no value.
		$first = key($net);
		$last = time();
		$day = 60*60*24;
		for ($i = $first; $i <= $last; $i += $day) {
			$label = date('l, dS M', $i);
			$rows[$label] = [
				'label' => $label,
				'value' => 0.0
			];
		}

		foreach ($net as $timestamp => $value) {
			$label = date('l, dS M', $timestamp);
			$rows[$label] = [
				'label' => $label,
				'value' => (float) $value
			];
		}

		return $this->render('Message:Mothership:ControlPanel::module:dashboard:area-graph', [
			'label'   => 'Total sales (week)',
			'keys' => [
				'label' => 'Day',
				'value' => 'Amount',
			],
			'rows'  => $rows,
			'numbers' => [
				[
					'value'   => $totalNet,
					'filters' => 'price',
				]
			]
		]);
	}
}