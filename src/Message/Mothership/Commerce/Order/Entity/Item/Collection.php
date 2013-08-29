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
}