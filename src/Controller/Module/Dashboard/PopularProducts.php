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
		$rows          = [];
		$dataset       = $this->get('statistics')->get('products.sales');
		$productsSales = $dataset->getRange($dataset::WEEK);

		uasort($productsSales, function($a, $b) {
			if ($a == $b) return 0;
			return $a < $b;
		});

		foreach ($productsSales as $unitID => $count) {
			$unit = $this->get('product.unit.loader')->getByID($unitID);
			if (! $unit) continue;

			$rows[] = [
				'label' => $unit->product->name . ', ' . implode(', ', $unit->options),
				'value' => $count,
			];

			if (count($rows) == 4) break;
		}

		return $this->render('Message:Mothership:ControlPanel::module:dashboard:bar-graph', [
			'label' => 'Popular products (week)',
			'keys' => [
				'label' => 'Product',
				'value' => 'Purchased',
			],
			'rows'  => $rows,
		]);
	}
}