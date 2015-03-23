<?php

namespace Message\Mothership\Commerce\Product\Price;

/**
 * An interface to allow uniform access to objects with prices. ie. Products and Units
 * 
 * @author Sam Trangmar-Keates
 */
interface PricedInterface
{
	/**
	 * Gets the associated prices. Returning an array is deprecated
	 * 
	 * @return PriceCollection|array the price collection
	 */
	public function getPrices();

	/**
	 * Get the current price of price type based on the given currencyID
	 * 
	 * @param  string $type       the type eg retail rrp etc
	 * @param  string $currencyID the currency to get the price for
	 * @return int                the price
	 */
	public function getPrice($type, $currencyID);

	/**
	 * Returns true if price set on this object
	 * @param  string  $type       the type e.g. retail
	 * @param  string  $currencyID the currency the price is in
	 * @return boolean             wheather the price is set
	 */
	public function hasPrice($type, $currencyID);
}