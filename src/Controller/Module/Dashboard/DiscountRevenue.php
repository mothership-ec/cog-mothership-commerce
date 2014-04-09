<?php

namespace Message\Mothership\Commerce\Controller\Module\Dashboard;

use Message\Cog\Controller\Controller;

/**
 * Discount revenue dashboard module.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class DiscountRevenue extends Controller
{
	const CACHE_KEY = 'dashboard.module.discount-revenue';
	const CACHE_TTL = 3600;

	/**
	 * Get the total gross and customer savings on discounted orders for the
	 * past 7 days.
	 *
	 * @return Message\Cog\HTTP\Response
	 */
	public function index()
	{
		if (false === $data = $this->get('cache')->fetch(self::CACHE_KEY)) {
			$since = strtotime(date('Y-m-d')) - (60 * 60 * 24 * 6);

			$totals = $this->get('db.query')->run("
				SELECT
					SUM(total_discount) as sum_total_discount,
					SUM(total_gross) as sum_total_gross
				FROM order_summary
				WHERE total_discount > 0
				AND created_at > {$since}
			");

			$data = [
				'total_discount' => $totals[0]->sum_total_discount,
				'total_gross'    => $totals[0]->sum_total_gross,
			];

			$this->get('cache')->store(self::CACHE_KEY, $data, self::CACHE_TTL);
		}

		return $this->render(
			'Message:Mothership:Commerce::module:dashboard:discount-revenue',
			$data
		);
	}
}