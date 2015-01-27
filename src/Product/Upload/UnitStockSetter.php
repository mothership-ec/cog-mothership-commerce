<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Stock\Location;
use Message\Mothership\Commerce\Product\Stock\StockManager;
use Message\Mothership\Commerce\Product\Stock\Movement\Reason\Reason;

class UnitStockSetter
{
	/**
	 * @var \Message\Mothership\Commerce\Product\Stock\StockManager
	 */
	private $_stockManager;

	/**
	 * @var \Message\Mothership\Commerce\Product\Stock\Movement\Reason\Reason
	 */
	private $_reason;

	/**
	 * @var \Message\Mothership\Commerce\Product\Stock\Location\Collection
	 */
	private $_locations;

	/**
	 * @var HeadingKeys
	 */
	private $_headingKeys;

	public function __construct(StockManager $stockManager, Reason $defaultReason, Location\Collection $locations, HeadingKeys $headingKeys)
	{
		$this->_stockManager = $stockManager;
		$this->_reason       = $defaultReason;
		$this->_locations    = $locations;
		$this->_headingKeys  = $headingKeys;
	}

	public function setStockLevel(Unit $unit, array $row, $location = 'web')
	{
		$stockKey = $this->_headingKeys->getKey('stock');

		if (!is_string($location) && !$location instanceof Location\Location) {
			throw new \InvalidArgumentException('Location must be either a string or an instance of Location');
		}
		elseif (!array_key_exists($stockKey, $row)) {
			throw new \LogicException('No stock column in row!');
		}

		$stockLevel = (int) $row[$stockKey];

		if ($stockLevel > 0) {
			$location = ($location instanceof Location\Location) ? $location : $this->_locations->get($location);
			$this->_stockManager->setReason($this->_reason);
			$this->_stockManager->set($unit, $location, $stockLevel);
		}
	}
}