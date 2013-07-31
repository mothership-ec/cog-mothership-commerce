<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Item extends Controller
{
	protected $_order;
	protected $_items;

	public function itemOverview($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);
		$this->_items = $this->get('order.item.loader')->getByOrder($this->_order);

		return $this->render('::order:item:item_overview', array(
			'items' => $this->_items,
			'order' => $this->_order,
		));
	}

	public function items($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);
		$this->_items = $this->get('order.item.loader')->getByOrder($this->_order);

		return $this->render('::order:item:items', array(
			'items' => $this->_items,
			'order' => $this->_order,
		));
	}

}
