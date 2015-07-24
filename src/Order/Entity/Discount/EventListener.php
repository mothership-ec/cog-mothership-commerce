<?php

namespace Message\Mothership\Commerce\Order\Entity\Discount;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Status\Status as BaseStatus;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

/**
 * Order discount event listener.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::CREATE_START => array(
				array('resetDiscountAmounts',	300),
				array('setDiscountItems',		150),
				array('calculateItemDiscount',	100),
			),
			OrderEvents::ASSEMBLER_UPDATE => array(
				array('resetDiscountAmounts',	300),
				array('setDiscountItems',		150),
				array('calculateItemDiscount',	100),
			),
		);
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
			if (count($discount->items) <= 0) {
				$discount->items = $event->getOrder()->items;
			}
		}
	}

	/**
	 * Resets the item's discount and sets the amount to 0 if the discount
	 * is a percentage one.
	 *
	 * @param Event $event The event object
	 */
	public function resetDiscountAmounts(Event\Event $event)
	{
		foreach ($event->getOrder()->items as $item) {
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
	 * items in the discount.
	 *
	 * @param Event $event The event object
	 */
	public function calculateItemDiscount(Event\Event $event)
	{
		$this->resetDiscountAmounts($event);

		$flat        = [];
		$percentages = [];

		foreach ($event->getOrder()->discounts as $discount) {
			if ($discount->percentage) {
				$percentages[] = $discount;
			} else {
				$flat[] = $discount;
			}
		}

		foreach ($flat as $discount) {
			$totalBasePrice = 0;
			foreach($discount->items as $item) {
				$totalBasePrice += $item->basePrice;
			}

			if ($totalBasePrice === 0 ) {
				continue;
			}

			$prorateHelper = $this->get('helper.prorate')
				->setGetBasisPercentage(
					function($item) use ($totalBasePrice)
					{
						return $item->basePrice / $totalBasePrice;
					}
				)
				->setAssignProrateAmount(
					function($item, $proratedValue)
					{
						$item->discount += $proratedValue;
					}
				);
			$prorateHelper->prorateValue($discount->amount, $discount->items->all());
		}

		foreach ($percentages as $discount) {
			foreach ($discount->items as $item) {
				$amount            = $item->getDiscountedPrice() * ($discount->percentage / 100);
				$item->discount   += $amount;
				$discount->amount += $amount;
			}
		}
	}
}