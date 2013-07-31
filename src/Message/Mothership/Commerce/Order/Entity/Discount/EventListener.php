<?php

namespace Message\Mothership\Commerce\Order\Entity\Discount;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Status\Status as BaseStatus;

use Message\Cog\Event\SubscriberInterface;

/**
 * Order discount event listener.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(OrderEvents::CREATE_START => array(
			array('setDiscountItems',     200),
			array('resetDiscountAmounts', 150),
			array('calculateItemDiscount', 100),
		));
	}

	/**
	 * Set the items the discount is applicable to.
	 *
	 * @todo once the discounts system is built, inspect this to find out which
	 *       products a discount should apply to (if discount code recognised)
	 *
	 * @param Event $event The event object
	 */
	public function setDiscountItems(Event\Event $event)
	{
		foreach ($event->getOrder()->discounts as $discount) {
			if (empty($discount->items)) {
				$discount->items = $event->getOrder()->items->all();
			}
		}
	}

	/**
	 *
	 *
	 * @param Event $event The event object
	 */
	public function resetDiscountAmounts(Event\Event $event)
	{
		foreach ($event->getOrder()->getItems() as $item) {
			$item->discount = 0;
		}

		foreach ($event->getOrder()->discounts as $discount) {
			if ($discount->percentage) {
				$discount->amount = 0;
			}
		}
	}

	/**
	 * Set the discount amounts on all items in the order before it is created.
	 *
	 * If the discount is a fixed-amount discount, this is pro-rated across
	 * items in the order.
	 *
	 * @param Event $event The event object
	 */
	public function calculateItemDiscount(Event\Event $event)
	{
		// Loop through discounts
		foreach ($event->getOrder()->discounts as $discount) {
			// Loop through items this discount applies to
			foreach ($discount->items as $item) {
				if ($discount->percentage) {
					$amount            = $item->listPrice * ($discount->percentage / 100);
					$item->discount   += $amount;
					$discount->amount += $amount;
				}
				else {
					// TODO: do the complex pro-rating shiz
				}
			}
		}
	}
}