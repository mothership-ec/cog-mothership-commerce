<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Event\Event as BaseEvent;

class Event extends BaseEvent
{
	protected $_unit;

	public function __construct(Unit $unit)
	{
		$this->_unit = $unit;
	}

	public function getUnit()
	{
		return $this->_unit;
	}
}