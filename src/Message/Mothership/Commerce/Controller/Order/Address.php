<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Address extends Controller
{
	protected $_order;
	protected $_addresses;

	public function summary($orderId)
	{
		return $this->_loadOrderAndAddressesAndRender($orderId, 'Message:Mothership:Commerce::order:address:summary');
	}

	public function addresses($orderId)
	{
		return $this->_loadOrderAndAddressesAndRender($orderId, 'Message:Mothership:Commerce::order:address:addresses');
	}

	protected function _loadOrderAndAddressesAndRender($orderId, $view)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);
		$this->_addresses = $this->get('order.address.loader')->getByOrder($this->_order);

		return $this->render($view, array(
			'addresses' => $this->_addresses,
			'order' => $this->_order,
		));
	}

}
