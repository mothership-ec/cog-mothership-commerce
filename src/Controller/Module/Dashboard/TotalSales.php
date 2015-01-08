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
	public function index($currency)
	{
		$days = $this->get('db.query')->run('
			SELECT
				created_at AS date,
				SUM(product_gross) AS gross
			FROM
				order_summary
			WHERE
				FROM_UNIXTIME(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND NOW()
			AND
				currency_id = :id?s
			GROUP BY
				DAY(FROM_UNIXTIME(created_at))

			ORDER BY
				created_at ASC
			', [
				'id' => $currency
		]);

		$total = $this->get('db.query')->run('
			SELECT
				SUM(product_gross) AS gross
			FROM
				order_summary
			WHERE
				FROM_UNIXTIME(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND NOW()
			AND
				currency_id = :id?s
			', [
				'id' => $currency
		])->flatten();

		$rows = [];

		if ($days) {
			//$i = 0;
			$day = 60*60*24;

			$first = time() - ($day * 6);

			for ($i = 0; $i <= 6; $i++) {
				$label = date('l, jS F Y', $first);
				$first += $day;
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
			'label'   => 'Total sales this week (excl shipping) - ' . $currency,
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
			],
			'currency' => $currency,
		]);
	}
}