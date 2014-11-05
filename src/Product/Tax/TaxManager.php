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
	public function getNetPrice($price, $tax)
	{
		return $this->_taxStrategy->getNetPrice($price, $tax);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getGrossPrice($price, $tax)
	{
		return $this->_taxStrategy->getGrossPrice($price, $tax);
	}
}