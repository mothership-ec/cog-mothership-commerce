<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event\Event as OrderEvent;

use Message\User\Event as UserEvents;
use Message\Mothership\Commerce\Order\Event\ValidateEvent;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Mothership\Commerce\Order\Entity\Address\Address;
use Message\Cog\Event\Event as BaseEvent;
use Message\Mothership\Ecommerce;
use Message\User\User;
use Message\User\AnonymousUser;

/**
 * Basket Assembler for adding addresses and users to the basket
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class AssemblerListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::ASSEMBLER_UPDATE => array(
				array('validateItems', 500),
			),
		);
	}

	/**
	 * Validates all items in the assembler.
	 *
	 *  * If the amount of a unique unit in the assembler is greater than the
	 *    amount in stock in the sell location, the items are reduced to the
	 *    amount available (or removed if there are none in stock).
	 *
	 * If any items are removed, some warning feedback is added for the user.
	 *
	 * @todo This should only happen for the basket, not all assemblers (see
	 *       issue: https://github.com/messagedigital/cog-mothership-commerce/issues/196)
	 *
	 * @param OrderEvent $event The event object
	 */
	public function validateItems(OrderEvent $event)
	{
		$order        = $event->getOrder();
		$unitLoader   = $this->get('product.unit.loader')->includeInvisible(true)->includeOutOfStock(true);
		$locations    = $this->get('stock.locations');
		$sellLocation = $locations->getRoleLocation($locations::SELL_ROLE);
		$addFlash     = false;

		// @todo Quick fix to stop it taking items out of repairs. This will need changing!
		if ($order->type == 'web') {
			foreach ($order->items->getRows() as $row) {
				$unit  = $unitLoader->getByID($row->first()->unitID);
				$stock = $unit->getStockForLocation($sellLocation);

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
		}

		if ($addFlash) {
			$this->get('http.session')->getFlashBag()->add(
				'warning',
				'Some of the items in your basket are no longer available and have been removed.'
			);
		}
	}
}