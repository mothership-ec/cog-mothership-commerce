<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement;

use Message\Cog\ValueObject\Authorship;

class Movement
{
	public $id;

	public $authorship;
	public $reason;
	public $note = '';

	public $adjustments = array();

	public function __construct()
	{
		$this->authorship = new Authorship;
	}

	public function addAdjustment(Adjustment $adjustment)
	{
		$this->_adjustments[] = $adjustment;
	}
}