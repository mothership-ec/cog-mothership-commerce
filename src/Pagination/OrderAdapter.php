<?php

namespace Message\Mothership\Commerce\Pagination;

use Message\Cog\Pagination\Adapter\AdapterInterface;
use Message\Mothership\Commerce\Order\Loader as OrderLoader;

class OrderAdapter implements AdapterInterface
{
	private $_orderLoader;
	private $_statuses;

	public function __construct(OrderLoader $orderLoader)
	{
		$this->_orderLoader  = $orderLoader;
	}

	public function getCount()
	{
		return $this->_orderLoader->count($this->_statuses);
	}

	public function getSlice($offset, $limit)
	{
		if ($this->_statuses) {
			return $this->_orderLoader->getByStatus($this->_statuses, $offset, $limit);
		}

		return $this->_orderLoader->getBySlice($offset, $limit);
	}

	public function setStatuses(array $statuses)
	{
		$this->_statuses = $statuses;
	}
}