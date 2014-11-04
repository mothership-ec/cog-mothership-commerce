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
	 * Gets the display price based on the tax strategy
	 * 
	 * @param  double                    $price The price to calculate tax for
	 * @param  TaxRate|TaxRateCollection $tax   The tax rate/collection of tax rates to use
	 * @return double                           The adjusted price
	 */
	public function getDisplayPrice($price, $tax);
	{
		return $this->_taxStrategy->getDisplayPrice($price, $tax);
	}

	/**
	 * Gets the net price ignoring the tax strategy
	 * 
	 * @param  double                    $price The price to calculate tax for
	 * @param  TaxRate|TaxRateCollection $tax   The tax rate/collection of tax rates to use
	 * @return double                           The adjusted price
	 */
	public function getNetPrice($price, $tax);
} 