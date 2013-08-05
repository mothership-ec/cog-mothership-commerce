<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Dispatch extends Controller
{
	protected $_order;
	protected $_dispatches;

	public function dispatches($orderID)
	{
		return $this->_loadOrderAndDispatchesAndRender($orderID, 'Message:Mothership:Commerce::order:detail:dispatch:dispatches');
	}

	protected function _loadOrderAndDispatchesAndRender($orderID, $view)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_dispatches = $this->get('order.dispatch.loader')->getByOrder($this->_order);
		
		//d($this->_dispatches);


		return $this->render($view, array(
			'dispatches' => $this->_dispatches,
			'order' => $this->_order,
		));
	}

}
