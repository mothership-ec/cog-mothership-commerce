<?php

namespace Message\Mothership\Commerce\Event;

use Message\Cog\Event\Event as BaseEvent;

class CurrencyChangeEvent extends BaseEvent
{
	private $_currency;

	public function __construct($currency)
	{
		$this->_currency = $currency;
	}

	public function getCurrency()
	{
		return $this->_currency;
	}
}