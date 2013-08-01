<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Item extends Controller
{
	protected $_order;
	protected $_items;

	public function itemSummary($orderId)
	{
		return $this->_loadOrderAndItemsAndRender($orderId, 'Message:Mothership:Commerce::order:item:summary');
	}

	public function items($orderId)
	{
		//TODO: item status history!!
		return $this->_loadOrderAndItemsAndRender($orderId, 'Message:Mothership:Commerce::order:item:items');
	}

	public function sidebar($orderId)
	{
		return $this->_loadOrderAndItemsAndRender($orderId, 'Message:Mothership:Commerce::order:item:sidebar');
	}

	protected function _loadOrderAndItemsAndRender($orderId, $view)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);
		$this->_items = $this->get('order.item.loader')->getByOrder($this->_order);

		return $this->render($view, array(
			'items' => $this->_items,
			'order' => $this->_order,
		));
	}

}
