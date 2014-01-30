<?php

namespace Message\Mothership\Commerce\Order\Entity\Discount;

use Message\Mothership\Commerce\Order\Entity\Collection as BaseCollection;

class Collection extends BaseCollection
{
	public function getByCode($code)
	{
		$return = array();

		foreach ($this->all() as $discount) {
			if ($code === $discount->code) {
				$return[] = $discount;
			}
		}

		return $return;
	}

	public function codeExists($code)
	{
		foreach ($this->all() as $discount) {
			if ($code === $discount->code) {
				return true;
			}
		}

		return false;
	}
}