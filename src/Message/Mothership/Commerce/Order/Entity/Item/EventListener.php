<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Status\Status as BaseStatus;

use Message\Cog\Event\SubscriberInterface;

/**
 * Order item event listener.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener implements SubscriberInterface
{
	protected $_defaultStatus;

	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(Event::CREATE_START => array(
			array('calculateTax'),
			array('setDefaultStatus'),
		));
	}

	/**
	 * Constructor.
	 *
	 * @param BaseStatus $defaultStatus The default status to set on new order items
	 */
	public function __construct(BaseStatus $defaultStatus)
	{
		$this->_defaultStatus = $defaultStatus;
	}

	/**
	 * Set the default statuses on all items that don't already have a status set.
	 *
	 * @param Event $event The event object
	 */
	public function setDefaultStatus(Event $event)
	{
		foreach ($event->getOrder()->items as $item) {
			if (!$item->status) {
				$item->status = new Status($this->_defaultStatus->code, $this->_defaultStatus->name);
			}
		}
	}

	/**
	 * Calculate the gross, tax and net amounts for each item in an order before
	 * it gets created in the database.
	 *
	 * @param Event $event The event object
	 */
	public function calculateTax(Event $event)
	{
		foreach ($event->getOrder()->items as $item) {
			$item->gross = round($item->listPrice - $item->discount, 2);
			$item->tax   = round(($item->gross / (100 + $item->taxRate)) * $item->taxRate, 2);
			$item->net   = round($item->gross - $item->tax, 2);
		}
	}
}