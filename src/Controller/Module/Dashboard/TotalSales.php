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
		$days = $this->get('db.query')->run("
			SELECT
				created_at AS date,
				SUM(product_gross) AS gross
			FROM
				order_summary
			WHERE
				FROM_UNIXTIME(created_at) BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW()
			GROUP BY
				DAY(FROM_UNIXTIME(created_at))
			ORDER BY
				created_at DESC
			");

		$total = $this->get('db.query')->run("
			SELECT
				SUM(product_gross) AS gross
			FROM
				order_summary
			WHERE
				FROM_UNIXTIME(created_at) BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW()
			")->flatten();

		$rows = [];

		if ($days) {
			$i = 0;
			$last = time();

			$day = 60*60*24;
			for ($i = 0; $i <= 6; $i++) {
				$label = date('l, jS F Y', $last);
				$last -= $day;
				$rows[$label] = [
					'label' => $label,
					'value' => 0.0
				];
			}

			foreach ($days as $day => $value) {
				$label = date('l, jS F Y', $value->date);
				$rows[$label] = [
					'label' => $label,
					'value' => (float) $value->gross
				];
			}
		}

		return $this->render('Message:Mothership:ControlPanel::module:dashboard:area-graph', [
			'label'   => 'Total sales (week)',
			'keys' => [
				'label' => 'Day',
				'value' => 'Amount',
			],
			'rows'  => $rows,
			'filterPrice' => true,
			'numbers' => [
				[
					'value'   => $total['0'],
				]
			]
		]);
	}
}