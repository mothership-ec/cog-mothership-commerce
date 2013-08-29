<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement;

use Message\Cog\ValueObject\Authorship;

class Movement
{
	public $id;

	public $authorship;
	public $reason;
	public $note = '';
	public $automated;

	public $adjustments = array();

	public function __construct()
	{
		$this->authorship = new Authorship;
	}

	public function addAdjustment(Adjustment\Adjustment $adjustment)
	{
		$adjustment->movement = $this;

		foreach($this->adjustments as $curAdjustment)
		{
			if($curAdjustment->unit === $adjustment->unit && $curAdjustment->location === $adjustment->location) {
				$curAdjustment->delta = $curAdjustment->delta + $adjustment->delta;

				return $this;
			}
		}

		$this->adjustments[] = $adjustment;
		return $this;
	}
}