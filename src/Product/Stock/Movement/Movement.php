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

		foreach($this->adjustments as $curAdjustment) {
			if($curAdjustment->unit === $adjustment->unit && $curAdjustment->location === $adjustment->location) {
				$curAdjustment->delta = $curAdjustment->delta + $adjustment->delta;

				return $this;
			}
		}

		$this->adjustments[] = $adjustment;
		return $this;
	}

	public function getAdjustmentPresenters()
	{
		$adjustmentPresenters = array();
		$localAdjustments = $this->adjustments;
		$alreadyAdded = array();

		foreach($localAdjustments as $i => $iAdjustment) {
			if(in_array($i, $alreadyAdded)) {
				continue;
			}

			$adjustmentPresenter = new Adjustment\AdjustmentPresenter;
			$adjustmentPresenter->addAdjustment($iAdjustment);

			foreach($localAdjustments as $j => $jAdjustment) {
				if(in_array($j, $alreadyAdded)) {
					continue;
				}

				$jAdjustment = $localAdjustments[$j];
				if($iAdjustment->unit === $jAdjustment->unit && $iAdjustment->delta === (-1 * $jAdjustment->delta)) {
					$adjustmentPresenter->addAdjustment($jAdjustment);

					$alreadyAdded[] = $j;
					break;
				}
			}
			$alreadyAdded[] = $i;

			$adjustmentPresenters[] = $adjustmentPresenter;
		}

		return $adjustmentPresenters;
	}
}