<?php

namespace Message\Mothership\Commerce\Product\Tax;

use Message\Mothership\Commerce\Address\Address;
use Message\Mothership\Commerce\Product\Product;
use Message\Cog\ValueObject\Collection;

/**
 * @author Samuel Trangmar-Keates sam@message.co.uk
 *
 * An interface for a tax manager. The tax manager should be the go to class for
 * dealing with taxes.
 */
interface TaxManagerInterface
{
	/**
	 * Set the tax strategy
	 */
	public function setTaxStrategy(Strategy\TaxStrategyInterface $strategy);

	/**
	 * Gets the tax strategy
	 */
	public function getTaxStrategy();

	/**
	 * Add a possible tax rate
	 * 
	 * @param Rate\TaxRate $strategy The tax strategy to add
	 */
	public function addTaxRate(Rate\TaxRate $rate);

	/**
	 * Returns the available strategies
	 * 
	 * @return Message\Cog\ValueObject\Collection The strategies
	 */
	public function getTaxRates();

	/**
	 * Set the default tax rate. This will be used if no tax rate found for an address
	 * or if tax strategy is inclusive
	 * 
	 * @param string|Rate\TaxRate $rate The default rate at which to tax
	 */
	public function setDefaultTaxRate($rate);

	/**
	 * Get the tax rate for an item to an address
	 * 
	 * @param  Product    $product The product to calulate tax for
	 * @param  Address    $address The address delvered to
	 * @return Collection          The tax rates to be applied
	 */
	public function getTaxRates(Product $product, Address $address);
} 