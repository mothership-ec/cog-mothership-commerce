<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Address extends Controller
{
	protected $_order;
	protected $_addresses;

	public function addressOverview($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);
		$this->_addresses = $this->get('order.address.loader')->getByOrder($this->_order);

		return $this->render('::order:address_overview', array(
			'addresses' => $this->_addresses,
			'order' => $this->_order,
		));
	}

	public function addresses($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);
		$this->_addresses = $this->get('order.address.loader')->getByOrder($this->_order);

		return $this->render('::order:addresses', array(
			'addresses' => $this->_addresses,
			'order' => $this->_order,
		));
	}

}
