<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Cog\Event\EventListener;
use Message\Cog\Event\SubscriberInterface;
use Message\Mothership\Commerce\Events;
use Message\Mothership\Commerce\Event\CurrencyChangeEvent;
use Message\Mothership\Commerce\Order\Assembler;

class CurrencyChangeListener extends EventListener implements SubscriberInterface
{
	private $_basket;

	public function __construct(Assembler $basket)
	{
		$this->_basket = $basket;
	}

	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return [
			Events::CURRENCY_CHANGE => [
				['currencyChange'],
			],
		];
	}


	public function currencyChange(CurrencyChangeEvent $e)
	{
		$this->_basket->updateCurrency($e->getCurrency());
	}
}