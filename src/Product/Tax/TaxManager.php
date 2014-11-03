<?php

namespace Message\Mothership\Commerce\Product\Tax;

use Message\Mothership\Commerce\Address\Address;
use Message\Mothership\Commerce\Product\Product;

class TaxManager implements TaxManagerInterface 
{
	private $_taxRates;
	private $_taxStrategy;
	private $_defaultTaxKey = null;
	private $_taxResolver;

	public function __construct(TaxResolver $taxResolver)
	{
		$this->_taxResolver = $taxResolver;
		$this->_taxRates = new Rate\TaxRateCollection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTaxStrategy(Strategy\TaxStrategyInterface $strategy)
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
	public function addTaxRate(Rate\TaxRate $rate)
	{
		$this->_taxRates->add($rate);

		return $this;
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
	public function setDefaultTaxRate($rate)
	{
		if ($rate instanceof Rate\TaxRate) {
			if (!$_taxRates->contains($rate->getName())) {
				$this->addTaxRate($rate);
			}

			$this->setDefaultTaxRate($rate->getName());
		} else {
			$this->_defaultTaxKey = $rate;
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @todo Implement this. Use TaxResolver to return the tax rates.
	 */
	public function getTaxRates(Product $product, Address $address)
	{

	}
} 