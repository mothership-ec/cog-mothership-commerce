<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement;

use Message\Cog\ValueObject\Authorship;

class Movement
{
	public $id;

	public $authorship;
	public $reason;
	public $note = '';

	protected $_adjustments = array();
	protected $_adjustmentLoader;

	public function __construct()
	{
		// TODO: Add adjustmentLoader
		$this->authorship = new Authorship;

	}

	public function addAdjustment(Adjustment $adjustment)
	{
		$this->_adjustments[] = $adjustment;
	}

	public function getAdjustments()
	{
		if (!$this->_adjustments) {
			$this->_loadAdjustments();
		}

		return $this->_adjustments;
	}

	protected function _loadAdjustments()
	{
		$this->_adjustments = $this->_adjustmentLoader->getByMovement($this);
	}
}