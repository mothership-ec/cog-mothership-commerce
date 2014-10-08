<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\ProductEntityLoaderInterface;
use Message\Cog\ValueObject\Collection as BaseCollection;

/**
 * Collection of units
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Collection extends BaseCollection
{
	protected function _configure()
	{
		$this->setType('Message\\Mothership\\Commerce\\Product\\Unit\\Unit');
		$this->setKey('id');

	}

	public function getByCriteria($includeOutOfStock = true, $includeInvisible = false)
	{
		$return = [];

		foreach ($this->all() as $id => $unit) {
			$outOfStockCriteria = !$unit->isOutOfStock() || $includeOutOfStock;
			$invisibleCriteria  = $unit->visible || $includeInvisible;

			if ($outOfStockCriteria && $invisibleCriteria) {
				$return[$id] = $unit;
			}
		}

		return $return;
	}

	public function getByProperty($property, $value)
	{
		$return = [];

		foreach ($this->all() as $id => $unit) {
			if (property_exists($unit, $property) && $unit->{$property} == $value) {
				$return[$id] = $unit;
			}
		}

		return $return;
	}
}