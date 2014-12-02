<?php

namespace Message\Mothership\Commerce\Product\Tax\Strategy;

use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection;
use Message\Mothership\Commerce\Product\Tax\Resolver\TaxResolver;
use Message\Mothership\Commerce\Address\Address;

class InclusiveTaxStrategy extends AbstractStrategy
{
	private $_includedRate;
	private $_resolver;
	private $_companyAddress;

	public function __construct(TaxResolver $resolver, Address $companyAddress)
	{
		$this->_resolver       = $resolver;
		$this->_companyAddress = $companyAddress;

		// default to generic 'product' tax
		$this->_includedRate = $resolver->getTaxRates('product', $companyAddress);
	}

	/**
	 * Update the included rate for whatever product type
	 * 
	 * @param string $type The product type
	 */
	public function setProductType($type)
	{
		$this->_includedRate = $this->_resolver->getTaxRates($type, $this->_companyAddress);
	}

	/**
	 * {@inheritDocs}
	 */
	public function getNetPrice($price)
	{
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException('Price must be numeric, ' . $price . ' given');
		}

		$rate = $this->getIncludedTaxRate();

		return $price / (1 + $rate/100);
	}

	/**
	 * Get the default strategy address
	 * @return Address the address used to resolve included tax
	 */
	public function getDefaultStrategyAddress()
	{
		return $this->_companyAddress;
	}

	/**
	 * {@inhertDoc}
	 */
	public function getIncludedTaxRate()
	{
		return ($this->_includedRate instanceof TaxRate) ? $this->_includedRate->getRate() : $this->_includedRate->getTotalTaxRate();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'inclusive';
	}
}