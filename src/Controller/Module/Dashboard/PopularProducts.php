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
	/**
	 * Get the most ordered products in the past 7 days.
	 *
	 * @return Message\Cog\HTTP\Response
	 */
	public function index()
	{
		$rows         = [];
		$productsSales = $this->get('stats')->getValues('products.sales', strtotime('7 days ago'));

		uasort($productsSales, function($a, $b) {
			if ($a == $b) return 0;
			return $a < $b;
		});

		$productsSales = array_slice($productsSales, 0, 4);

		foreach ($productsSales as $productID => $count) {
			$rows[] = [
				'label' => $this->get('product.loader')->getByID($productID)->name,
				'value' => $count,
			];
		}

		return $this->render('Message:Mothership:ControlPanel::module:dashboard:bar-graph',
			'label' => 'Popular products (week)',
			'keys' => [
				'label' => 'Product',
				'value' => 'Purchased',
			],
			'rows'  => $rows,
		);
	}
}