<?php

namespace Message\Mothership\Commerce\Order\EventListener\Assembler;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event\Event as OrderEvent;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;

/**
 * Event listeners for checking that sufficient stock is available to fulfill
 * items in an order that is being assembled.
 *
 * @author Danny Hannah <danny@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class StockCheckListener extends BaseListener implements SubscriberInterface
{
	protected $_orderTypes = [];

	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::ASSEMBLER_UPDATE => array(
				array('checkStock', 500),
			),
		);
	}

	/**
	 * Constructor.
	 *
	 * @param array|string $applicableOrderTypes Order type(s) the listeners
	 *                                           should apply to
	 */
	public function __construct($applicableOrderTypes)
	{
		if (!is_array($applicableOrderTypes)) {
			$applicableOrderTypes = array($applicableOrderTypes);
		}

		$this->_orderTypes = $applicableOrderTypes;
	}

	/**
	 * Set a new order type that the listeners should apply to.
	 *
	 * Adding a new order type using this method will make the stock check
	 * listeners apply to any order where the "type" property matches.
	 *
	 * @param string $type The order type to allow
	 */
	public function addOrderType($type)
	{
		$this->_orderTypes[] = $type;
	}

	/**
	 * Checks the stock level for all items in an order **only** if the order's
	 * type is set as an applicable order type.
	 *
	 * If the quantity of a particular unit in the order is greater than the
	 * amount in the defined stock location, the extra items are removed so the
	 * quantity matches the stock level and a flash message is added to the
	 * session.
	 *
	 * @param OrderEvent $event The event object
	 */
	public function checkStock(OrderEvent $event)
	{
		$order = $event->getOrder();

		if (!in_array($order->type, $this->_orderTypes)) {
			return false;
		}

		$addFlash   = false;
		$unitLoader = $this->get('product.unit.loader')
			->includeInvisible(true)
			->includeOutOfStock(true);

		foreach ($order->items->getRows() as $row) {
			$unit  = $unitLoader->getByID($row->first()->unitID);
			$stock = $unit->getStockForLocation($row->first()->stockLocation);

			if ($row->getQuantity() > $stock) {
				$amountToRemove = $row->getQuantity() - $stock;
				$addFlash       = true;

				foreach ($row as $item) {
					if ($amountToRemove < 1) {
						break;
					}

					$order->items->remove($item);
					$amountToRemove--;
				}
			}
		}

		if ($addFlash) {
			$this->get('http.session')->getFlashBag()->add(
				'warning',
				'Some of the items in your basket are no longer available and have been removed.'
			);
		}
	}
}