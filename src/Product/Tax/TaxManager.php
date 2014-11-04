<?php

namespace Message\Mothership\Commerce\Product\Tax;

use Message\Mothership\Commerce\Address\Address;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Tax\Strategy\TaxStrategyInterface;

class TaxManager implements TaxManagerInterface 
{
	private $_defaultAddress;
	private $_taxStrategy;

	public function __construct(TaxStrategyInterface $strategy)
	{
		$this->_taxStrategy = $strategy;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTaxStrategy(TaxStrategyInterface $strategy)
	{
		$this->_taxStrategy = $taxStrategy;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTaxStrategy()
	{
		return $this->_taxStrategy;
	}
	

	/**
	 * {@inheritDoc}
	 */
	public function getTaxRates()
	{
		return $this->_taxRates;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getDisplayPrice($price, $tax)
	{
		return $this->_taxStrategy->getDisplayPrice($price, $tax);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNetPrice($price, $tax)
	{
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException('Price must be numeric, ' . $price . ' given');
		}

		if ($taxRate instanceof TaxRateCollection) {
			$tax = 0.000;
			foreach ($taxRate as $rate) {
				$tax += $rate->getTax($price);
			}
			$price += $tax;

			return $price;
		} else if ($taxRate instanceof TaxRate) {
			return $taxRate->getTaxedPrice($price);
		} else {
			throw new InvalidArgumentException('taxRate must be either instance of TaxRate or TaxRateCollection');
		}
	}
}