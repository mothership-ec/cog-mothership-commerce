<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;

use Message\Cog\Event\SubscriberInterface;
use Message\Mothership\Commerce\Product\Tax\Resolver\TaxResolver as Resolver;
use Message\Mothership\Commerce\Product\Tax\Exception;
use Message\Mothership\Commerce\Address\Address;
use Message\Mothership\Commerce\Product\Tax\Resolver\TaxResolverInterface;

/**
 * Order event listener for calculating the order totals.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class TotalsListener implements SubscriberInterface
{
	private $_taxResolver;

	public function __construct(TaxResolverInterface $resolver)
	{
		$this->_taxResolver = $resolver;
	}

	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::CREATE_START => array(
				array('calculateShippingTax'),
				array('setTotals', -900),
			),
			OrderEvents::ASSEMBLER_UPDATE => array(
				array('calculateShippingTax', -800),
				array('setTotals', -900),
			),
		);
	}

	/**
	 * Calculate the gross, tax and net amounts for shipping.
	 *
	 * The tax rate assigned to the shipping is always the highest tax rate of
	 * the items in the order.
	 *
	 * @param Event $event The event object
	 *
	 * @return false If there is no shipping cost and therefore nothing to be done
	 */
	public function calculateShippingTax(Event\Event $event)
	{
		$order = $event->getOrder();
		$address = $order->getAddress(Address::DELIVERY);

		if (!$order->shippingListPrice) {
			$order->shippingGross = 0;
			$order->shippingTax   = 0;
			$order->shippingNet   = 0;

			return false;
		}

		$taxResolver = $this->_taxResolver;

		// This should always work with a proper tax rate cfg setup.
		try {
			$taxRates = $taxResolver->getTaxRates(Resolver::DEFAULT_SHIPPING_TAX, $address);
			$rates = [];
			foreach ($taxRates as $rate) {
				$rates[$rate->getType()] = $rate->getRate();
			}
			$order->setShippingTaxes($rates);
		}
		// If not then no shipping tax rates set in cfg.
		// Revert to old logic.
		catch (Exception\TaxRateNotFoundException $e) {
			$rate = [];
			foreach ($order->items as $item) {
				if ($item->taxRate > $order->shippingTaxRate) {
					$rate = ['VAT' => $item->taxRate];
				}
			}
			$order->setShippingTaxes($rate);
		}
	}

	/**
	 * Set the totals on the order as the last event before the order is created.
	 *
	 * @param Event $event The event object
	 */
	public function setTotals(Event\Event $event)
	{
		$order = $event->getOrder();

		$order->updateTotals();
	}
}