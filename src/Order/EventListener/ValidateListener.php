<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Mothership\Commerce\Order\Order;
use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Status\Status;

use Message\Cog\Event\SubscriberInterface;

/**
 * Order event listener for validating the order before it is created.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ValidateListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(OrderEvents::CREATE_VALIDATE => array(
			array('checkProperties'),
		));
	}

	/**
	 * Run basic validation on the order before it is created.
	 *
	 * @param  Event\ValidateEvent $event The event object
	 */
	public function checkProperties(Event\ValidateEvent $event)
	{
		$order = $event->getOrder();

		if (!$order->status) {
			$event->addError('Order must have a status set');
		}
		else {
			if (!($order->status instanceof Status)) {
				$event->addError('Order status must be an instance of Order\Status\Status');
			}
		}

		if (!$order->type) {
			$event->addError('Order must have a type set');
		}

		if (!$order->currencyID) {
			$event->addError('Order must have a currency ID');
		}

		if (count($order->getItems()) <= 0) {
			$event->addError('Order must have items set');
		}

		if (!$this->_validateTax($order)) {
			$event->addError('Tax calculation error');
		}
	}

	/**
	 * Recount the item tax and check that it matches the order product tax
	 *
	 * @param Order $order
	 *
	 * @return bool
	 */
	private function _validateTax(Order $order)
	{
		$itemTax = 0;

		foreach ($order->getItems() as $item) {
			$itemTax += $item->getTax();
		}

		return $order->productTax == $itemTax;
	}
}