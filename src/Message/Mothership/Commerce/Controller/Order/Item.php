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
		$this->_loadOrderAndItems($orderId);

		$statuses = array();

		foreach($this->_items AS $item)
		{
			$statuses[$item->id] = $this->get('order.item.status.loader')->getHistory($item);
		}

		return $this->render('Message:Mothership:Commerce::order:item:items', array(
			'items' => $this->_items,
			'order' => $this->_order,
			'statuses' => $statuses,
		));
	}

	protected function _loadOrderAndItemsAndRender($orderId, $view)
	{
		$this->_loadOrderAndItems($orderId);

		return $this->render($view, array(
			'items' => $this->_items,
			'order' => $this->_order,
		));
	}

	protected function _loadOrderAndItems($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);
		$this->_items = $this->get('order.item.loader')->getByOrder($this->_order);
	}

}
