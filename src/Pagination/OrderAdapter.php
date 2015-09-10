<?php

namespace Message\Mothership\Commerce\Pagination;

use Message\Cog\Pagination\Adapter\AdapterInterface;
use Message\Mothership\Commerce\Order\Loader as OrderLoader;

class OrderAdapter implements AdapterInterface
{
	private $_orderLoader;
	private $_statuses;

	private $_orderBy;

	public function __construct(OrderLoader $orderLoader)
	{
		$this->_orderLoader  = $orderLoader;
	}

	public function orderBy($orderBy)
	{
		$this->_orderBy = $orderBy;

		return $this;
	}

	public function getCount()
	{
		return $this->_orderLoader->count($this->_statuses);
	}

	public function getSlice($offset, $limit)
	{
		$offset = $offset * $limit;

		if ($this->_orderBy) {
			$this->_orderLoader->orderBy($this->_orderBy);
		}

		if ($this->_statuses) {
			return $this->_orderLoader->getByStatus($this->_statuses, $offset, $limit);
		}

		return $this->_orderLoader->getBySlice($offset, $limit);
	}

	public function setStatuses(array $statuses)
	{
		$this->_statuses = $statuses;
		
		return $this;
	}
}