<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Address extends Controller
{
	protected $_order;
	protected $_addresses;

	public function summary($orderID)
	{
		return $this->_loadOrderAndAddressesAndRender($orderID, 'Message:Mothership:Commerce::order:detail:address:summary');
	}

	public function addresses($orderID)
	{
		return $this->_loadOrderAndAddressesAndRender($orderID, 'Message:Mothership:Commerce::order:detail:address:addresses');
	}

	protected function _loadOrderAndAddressesAndRender($orderID, $view)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_addresses = $this->get('order.address.loader')->getByOrder($this->_order);

		return $this->render($view, array(
			'addresses' => $this->_addresses,
			'order' => $this->_order,
		));
	}

}
