<?php

namespace Message\Mothership\Commerce\Product\Tax;

use Message\Mothership\Commerce\Address\Address;
use Message\Mothership\Commerce\Product\Product;
use Message\Cog\ValueObject\Collection;

class TaxManager implements TaxManagerInterface 
{
	private $_taxRates;
	private $_taxStrategy;
	private $_defaultTaxKey = null;
	private $_taxResolver;

	public function __construct(TaxResolver $taxResolver)
	{
		$this->_taxResolver = $taxResolver;
		$_taxRates = new Collection;
		$_taxRates
			->setType('Message\Mothership\Commerce\Product\Tax\Rate\TaxRate')
			->setKey(function($item) { return $item->getName(); })
		;
	}

	/**
	 * {@inheritDocs}
	 */
	public function setTaxStrategy(Strategy\TaxStrategyInterface $strategy)
	{
		$this->_taxStrategy = $taxStrategy;

		return $this;
	}

	/**
	 * {@inheritDocs}
	 */
	public function getTaxStrategy()
	{
		return $this->_taxStrategy;
	}

	/**
	 * {@inheritDocs}
	 */
	public function addTaxRate(Rate\TaxRate $rate)
	{
		$this->_taxRates->add($rate);

		return $this;
	}

	/**
	 * {@inheritDocs}
	 */
	public function getTaxRates()
	{
		return $this->_taxRates;
	}

	/**
	 * {@inheritDocs}
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
	 * {@inheritDocs}
	 *
	 * @todo Implement this. Use TaxResolver.
	 */
	public function getTaxRate(Product $product, Address $address)
	{

	}
} 