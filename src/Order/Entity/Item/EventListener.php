<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Status\Status as BaseStatus;
use Message\Mothership\Commerce\Order\Statuses;

use Message\Cog\Event\SubscriberInterface;

/**
 * Order item event listener.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener implements SubscriberInterface
{
	protected $_defaultStatus;
	protected $_itemEdit;

	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::ENTITY_CREATE => array(
				array('setDefaultActualPrice'),
				array('setBasePrice'),
				array('calculateTax'),
				array('setDefaultStatus'),
			),
			OrderEvents::CREATE_VALIDATE => array(
				array('checkItemSet')
			),
			OrderEvents::ASSEMBLER_UPDATE => array(
				array('setDefaultActualPrice'),
				array('setBasePrice'),
				array('calculateAllItemsTax'),
			),
			OrderEvents::STATUS_CHANGE => array(
				array('updateStatus'),
			),
		);
	}

	/**
	 * Constructor.
	 *
	 * @param BaseStatus $defaultStatus The default status to set on new order items
	 */
	public function __construct(BaseStatus $defaultStatus, Edit $itemEdit)
	{
		$this->_defaultStatus = $defaultStatus;
		$this->_itemEdit      = $itemEdit;
	}

	/**
	 * Update the items' statuses to match their parent order's status, where
	 * the order status is 'cancelled'.
	 *
	 * @param  EventTransactionalEvent $event
	 */
	public function updateStatus(Event\TransactionalEvent $event)
	{
		$order = $event->getOrder();

		if (Statuses::CANCELLED === $order->status->code) {
			$this->_itemEdit->setTransaction($event->getTransaction());
			$this->_itemEdit->updateStatus($order->items->all(), Statuses::CANCELLED);
		}
	}

	/**
	 * Default the actual price to the list price unless an actual price has
	 * been defined.
	 *
	 * @param Event\Event $event
	 */
	public function setDefaultActualPrice(Event\Event $event)
	{
		if ($event instanceof Event\EntityEvent
		 && $event->getEntity() instanceof Item) {
			$items = [$event->getEntity()];
		} else {
			$items = $event->getOrder()->items->all();
		}

		foreach ($items as $item) {
			if (!$item->actualPrice) {
				$item->actualPrice = $item->listPrice;
			}
		}
	}

	/**
	 * Set the `basePrice` value for the item(s).
	 *
	 * Base price is the same as the actual price unless the strategy is
	 * `inclusive` and the order is not taxable (i.e. a tax discount must be
	 * applied).
	 *
	 * @param Event\Event $event
	 */
	public function setBasePrice(Event\Event $event)
	{
		if ($event instanceof Event\EntityEvent
		 && $event->getEntity() instanceof Item) {
			$items = [$event->getEntity()];
		} else {
			$items = $event->getOrder()->items->all();
		}

		foreach ($items as $item) {
			$item->basePrice = $item->actualPrice;

			// Skip if tax strategy is exclusive or the order is taxable
			if ('exclusive' === $item->taxStrategy
			 || true === $item->order->taxable) {
				continue;
			}

			// Remove the tax discount
			$item->basePrice -= $this->_calculateInclusiveTax($item->actualPrice, $item->productTaxRate);
		}
	}

	/**
	 * Set the default statuses on all items that don't already have a status set.
	 *
	 * @param EntityEvent $event The event object
	 */
	public function setDefaultStatus(Event\EntityEvent $event)
	{
		$item = $event->getEntity();

		if (!($item instanceof Item)
		 || $item->status) {
			return false;
		}

		$item->status = new Status\Status($this->_defaultStatus->code, $this->_defaultStatus->name);
	}

	/**
	 * Calculate the gross, tax and net amounts for each item in an order before
	 * it gets created in the database.
	 *
	 * This event is skipped if the order is not taxable.
	 *
	 * @param EntityEvent $event The event object
	 */
	public function calculateTax(Event\EntityEvent $event)
	{
		$item = $event->getEntity();

		if (!($item instanceof Item)) {
			return false;
		}

		$this->_calculateTaxForItem($item);
	}

	public function calculateAllItemsTax(Event\Event $event)
	{
		foreach ($event->getOrder()->items as $item) {
			$this->_calculateTaxForItem($item);
		}
	}

	public function checkItemSet(Event\ValidateEvent $event)
	{
		if (count($event->getOrder()->items) < 1) {
			$event->addError('Order must have at least one item to be created');
		}
	}

	protected function _calculateTaxForItem(Item $item)
	{
		if (false === $item->order->taxable) {
			// Resetting the gross is important because if the tax strategy is
			// exclusive this will include the tax amount
			$item->gross   = $item->basePrice - $item->discount;
			$item->taxRate = 0;
			$item->tax     = 0;
			$item->net     = $item->gross;

			return;
		}

		// Set the tax rate to whatever the product's tax rate is, if not already set
		if (!$item->taxRate) {
			$item->taxRate = $item->productTaxRate;
		}

		// Set the gross to the list price minus the discount
		$item->gross = round($item->actualPrice - $item->discount, 2);

		// Calculate tax where the strategy is exclusive
		if ('exclusive' === $item->taxStrategy) {
			$item->tax    = round($item->gross * ($item->taxRate / 100), 2);
			$item->gross += $item->tax;
		}
		// Calculate tax where the strategy is inclusive
		else {
			$item->tax = $this->_calculateInclusiveTax($item->gross, $item->taxRate);
		}

		// Set the net value to gross - tax
		$item->net = round($item->gross - $item->tax, 2);
	}

	protected function _calculateInclusiveTax($amount, $rate)
	{
		return round(($amount / (100 + $rate)) * $rate, 2);
	}
}