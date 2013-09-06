<?php

namespace Message\Mothership\Commerce\Forex;

use Exception;

/**
 * Provides an interface to converting prices between currencies.
 *
 * @author Laurence Roberts <lsjroberts@gmail.com>
 */
class Converter {

	public $amount;
	public $from;
	public $to;
	
	protected $_rates;

	/**
	 * Constructor.
	 * 
	 * @param array       $rates  Conversion rates
	 * @param int|null    $amount Amount to convert
	 * @param string|null $from   Currency to convert from
	 * @param string|null $to     Currency to convert to
	 */
	public function __construct($rates, $amount = null, $from = null, $to = null)
	{
		$this->_rates = $rates;

		$this->amount = $amount;
		$this->from   = $from;
		$this->to     = $to;
	}

	/**
	 * Set the amount to convert.
	 * 
	 * @param  int $amount Amount to convert
	 * 
	 * @return Converter
	 */
	public function amount($amount)
	{
		$this->amount = $amount;
		return $this;
	}

	/**
	 * Set the currency to convert from.
	 * 
	 * @param  string $currency Currency to convert from
	 * 
	 * @return Converter
	 */
	public function from($currency)
	{
		$this->from = $currency;
		return $this;
	}

	/**
	 * Set the currency to convert to.
	 * 
	 * @param  string $currency Currency to convert to
	 * 
	 * @return Converter
	 */
	public function to($currency)
	{
		$this->to = $currency;
		return $this;
	}

	/**
	 * Get the converted amount.
	 * 
	 * @return float Converted amount
	 */
	public function get()
	{
		if ($this->amount === null) {
			throw new Exception("No amount set to convert.");
		}

		if ($this->from === null) {
			throw new Exception("No currency set to convert from.");
		}

		if ($this->to === null) {
			throw new Exception("No currency set to convert to.");
		}

		$rate = $this->_getRate($this->from, $this->to);

		return $this->amount * $rate;
	}

	/**
	 * Get the rate of conversion between two currencies.
	 * 
	 * @param  string $from Currency to convert from
	 * @param  string $to   Currency to convert to
	 * 
	 * @return float        Exchange rate
	 */
	protected function _getRate($from, $to)
	{
		$rates = $this->_convertRates($from);

		if (! isset($rates[$to])) {
			throw new Exception(sprintf("The currency '%' is not valid", $to));
		}

		return $rates[$to];
	}

	/**
	 * Get the exchange rates from a currency.
	 * 
	 * @param  string $from Currency to set as the base for conversion
	 * 
	 * @return array        List of exchange rates
	 */
	protected function _convertRates($from)
	{
		$rates = $this->_rates;
		$base  = $rates[$from];

		if ($base != 1.00) {
			foreach ($rates as $c => $r) {
				$rates[$c] = 1 / ($r / $base);
			}
		}

		return $rates;
	}

}