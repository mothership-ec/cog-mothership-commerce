<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Item extends Controller
{
	protected $_order;
	protected $_items;

	public function summary($orderID)
	{
		return $this->_loadOrderAndItemsAndRender($orderID, 'Message:Mothership:Commerce::order:detail:item:summary');
	}

	public function items($orderID)
	{
		$this->_loadOrderAndItems($orderID);

		$statuses = array();

		foreach($this->_items AS $item)
		{
			$statuses[$item->id] = $this->get('order.item.status.loader')->getHistory($item);
		}

		return $this->render('Message:Mothership:Commerce::order:detail:item:items', array(
			'items' => $this->_items,
			'order' => $this->_order,
			'statuses' => $statuses,
		));
	}

	protected function _loadOrderAndItemsAndRender($orderID, $view)
	{
		$this->_loadOrderAndItems($orderID);

		return $this->render($view, array(
			'items' => $this->_items,
			'order' => $this->_order,
		));
	}

	protected function _loadOrderAndItems($orderID)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_items = $this->get('order.item.loader')->getByOrder($this->_order);
	}

}
