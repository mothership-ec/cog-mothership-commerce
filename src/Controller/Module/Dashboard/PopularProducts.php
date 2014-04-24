<?php

namespace Message\Mothership\Commerce\Controller\Module\Dashboard;

use Message\Cog\Controller\Controller;

/**
 * Popular products dashboard module.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class PopularProducts extends Controller
{
	const CACHE_KEY = 'dashboard.module.popular-products';
	const CACHE_TTL = 3600;

	/**
	 * Get the most ordered products in the past 7 days.
	 *
	 * @return Message\Cog\HTTP\Response
	 */
	public function index()
	{
		if (false === $data = $this->get('cache')->fetch(self::CACHE_KEY)) {
			$products = [];

			$since = strtotime(date('Y-m-d')) - (60 * 60 * 24 * 6);

			$items = $this->get('db.query')->run("
				SELECT
					product_id,
					COUNT(item_id) as num
				FROM order_item
				WHERE created_at > ?
				GROUP BY product_id
			", [$since]);

			foreach ($items as $item) {
				$products[] = [
					'product' => $this->get('product.loader')->getByID($item->product_id),
					'count'   => $item->num,
				];
			}

			usort($products, function($a, $b) {
				if ($a['count'] == $b['count']) return 0;
				return ($a['count'] < $b['count']);
			});
			$products = array_slice($products, 0, 4);

			$data = [
				'products' => $products,
			];

			$this->get('cache')->store(self::CACHE_KEY, $data, self::CACHE_TTL);
		}

		return $this->render(
			'Message:Mothership:Commerce::module:dashboard:popular-products',
			$data
		);
	}
}