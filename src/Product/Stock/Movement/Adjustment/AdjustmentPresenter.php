<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement\Adjustment;

use Message\Mothership\Commerce\Product\Stock\Location\Location;

class AdjustmentPresenter
{
	public $adjustments = array();

	public function addAdjustment(Adjustment $adjustment)
	{
		if(count($this->adjustments) > 0 && $adjustment->unit !== $this->getUnit()) {
			throw new \InvalidArgumentException('Adjustment must have the same unit as the other adjustments of this presenter.');
		}

		$this->adjustments[] = $adjustment;
	}

	public function getAdjustment(Location $location)
	{
		foreach($this->adjustments as $adjustment) {
			if($adjustment->location == $location) {
				return $adjustment;
			}
		}

		return false;
	}

	public function getUnit()
	{
		return $this->adjustments[0]->unit;
	}
}