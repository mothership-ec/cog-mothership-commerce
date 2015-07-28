<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Entity\Collection as BaseCollection;
use Message\Mothership\Commerce\Order\Status\Status;

class Collection extends BaseCollection
{
	public function getRows()
	{
		$rows = array();

		foreach ($this->all() as $item) {
			if (!array_key_exists($item->unitID, $rows)) {
				$rows[$item->unitID] = new Row;
			}

			$rows[$item->unitID]->add($item);
		}

		return $rows;
	}

	public function getByCurrentStatus(Status $status)
	{
		return $this->getByCurrentStatusCode($status->code);
	}

	public function getByCurrentStatusCode($code)
	{
		$return = array();

		foreach ($this->all() as $item) {
			if ($code === $item->status->code) {
				$return[] = $item;
			}
		}

		return $return;
	}

	public function getTotalBasePrice()
	{
		$return = 0;

		foreach ($this->all() as $item) {
			$return += $item->basePrice;
		}

		return $return;
	}

	public function getTotalDiscountedPrice()
	{
		$price = 0;

		foreach ($this->all() as $item) {
			$price += $item->getDiscountedPrice();
		}

		return $price;
	}

	public function getTotalNetPrice()
	{
		$net = 0;

		foreach ($this->all() as $item) {
			$net += $item->net;
		}

		return $net;
	}

	public function getTotalTax()
	{
		$tax = 0;

		foreach ($this->all() as $item) {
			$tax += $item->tax;
		}

		return $tax;
	}

	public function getTotalGrossPrice()
	{
		$gross = 0;

		foreach ($this->all() as $item) {
			$gross += $item->gross;
		}

		return $gross;
	}
}