<?php

namespace Message\Mothership\Commerce\Controller\Module\Dashboard;

use Message\Cog\Controller\Controller;

class TotalSales extends Controller
{
	const CACHE_KEY = 'dashboard.module.total-sales';
	const CACHE_TTL = 3600;

	/**
	 *
	 * @return
	 */
	public function index()
	{
		if (false === $data = $this->get('cache')->fetch(self::CACHE_KEY)) {
			$products = [];

			$since = strtotime(date('Y-m-d')) - (60 * 60 * 24 * 6);

			$data = $this->get('db.query')->run("SELECT DAYNAME(FROM_UNIXTIME(created_at)) as dow, SUM(total_net) as net, SUM(total_gross) as gross FROM order_summary WHERE created_at > {$since} GROUP BY DATE(FROM_UNIXTIME(created_at))");

			$total = 0;
			$days = [];
			foreach ($data as $day) {
				$days[] = $day;
				$total += $day->net;
			}

			$data = [
				'days'  => $days,
				'total' => $total,
			];

			$this->get('cache')->store(self::CACHE_KEY, $data, self::CACHE_TTL);
		}

		return $this->render('Message:Mothership:Commerce::module:dashboard:total-sales', $data);
	}
}