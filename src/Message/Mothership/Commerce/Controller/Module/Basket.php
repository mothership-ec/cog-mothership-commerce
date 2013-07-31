<?php

namespace Message\Mothership\Commerce\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Mothership\CMS\Page\Content;

class Basket extends Controller
{
	protected $_product;

	public function index()
	{
		$basket = $this->get('basket');
		$unit = $this->get('product.unit.loader')->includeInvisible(true)->includeOutOfStock(true)->getByID(126);

		$basketDisplay = array();
		foreach ($basket->getOrder()->items as $item) {
			if (!isset($basketDisplay[$item->unitID]['quantity'])) {
				$basketDisplay[$item->unitID]['quantity'] = 0;
			}

			$basketDisplay[$item->unitID]['item'] = $item;
			$basketDisplay[$item->unitID]['quantity'] += 1;
		}

		return $this->render('Message:Mothership:Commerce::product:basket', array(
			'order'	  => $basket->getOrder(),
			'basket'  => $basketDisplay,
		));
		$basket = $this->get('basket');
		$product = $this->get('product.loader')->getByID(36);
		$unit = $product->getUnit(126);
		d($basket->getOrder()->items->count());
		$basket->updateQuantity($unit, 3);
		de($basket->getOrder()->items->count());
		$item = array_pop($basket->getOrder()->items->all());
		$basket->removeItem($item);
		de($basket->getOrder()->items->count());
	}

}