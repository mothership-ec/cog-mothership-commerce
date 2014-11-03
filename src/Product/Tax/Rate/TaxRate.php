<?php

namespace Message\Mothership\Commerce\Product\Tax\Rate;

/**
 * @author Samuel Trangmar-Keates sam@message.co.uk
 *
 * A simple tax rate class
 */
class TaxRate
{
	private $_taxRate;
	private $_name;
	private $_type;

	/**
	 * @param number $rate The tax rate
	 * @param string $name The name/key. This should be unique
	 * @param string $type The type of tax eg VAT/QST/PST etc...
	 */
	public function __construct($rate, $type, $name)
	{
		$this->_taxRate = $rate;
		$this->_name    = $name;
		$this->_type    = $type;
	}

	/**
	 * Get the price with tax added
	 * 
	 * @param  double $price The price
	 * @return double        The price with tax added
	 */
	public function getTaxedPrice($price)
	{
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException('Price must be numeric, ' . $price .' given');
		}

		return $price + $this->getTax($price);
	}

	/**
	 * Gets the tax for the price
	 * 
	 * @param  double $price The price
	 * @return double        Tax that will be added to the price
	 */
	public function getTax($price)
	{
		if (!is_numeric($price)) {
			throw new \InvalidArgumentException('Price must be numeric, ' . $price .' given');
		}
		
		return $price * ($this->getRate() / 100);
	}

	/**
	 * Returns the type of tax
	 * 
	 * @return string The type of tax
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Returns the rate as a float
	 * 
	 * @return string The name of the tax rate
	 */
	public function getRate()
	{
		return $this->_taxRate;
	}

	/**
	 * Returns the name. This should be a unique key
	 * 
	 * @return string The name of the tax rate
	 */
	public function getName()
	{
		return $this->_name;
	}
}