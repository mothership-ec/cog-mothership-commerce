<?php

namespace Message\Mothership\Commerce\Forex;

use LogicException;
use InvalidArgumentException;

/**
 * Provides an interface to converting prices between currencies.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Converter {

	protected $_amount;
	protected $_from;
	protected $_to;
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
		$this->_amount = $amount;
		$this->_from   = $from;
		$this->_to     = $to;
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
		$this->_amount = (float) $amount;

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
		$this->_from = $currency;

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
		$this->_to = $currency;

		return $this;
	}

	/**
	 * Get the converted amount.
	 *
	 * @return float Converted amount
	 */
	public function get()
	{
		if ($this->_amount === null) {
			throw new LogicException("No amount set to convert.");
		}

		if ($this->_from === null) {
			throw new LogicException("No currency set to convert from.");
		}

		if ($this->_to === null) {
			throw new LogicException("No currency set to convert to.");
		}

		$rate = $this->getRate($this->_from, $this->_to);

		return (float) $this->_amount * $rate;
	}

	/**
	 * Get the rate of conversion between two currencies.
	 *
	 * @param  string $from Currency to convert from
	 * @param  string $to   Currency to convert to
	 *
	 * @return float        Exchange rate
	 */
	public function getRate($from, $to)
	{
		$rates = $this->_convertRates($from);

		if (! isset($rates[$to])) {
			throw new InvalidArgumentException(sprintf("The currency '%' is not valid", $to));
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

		if (! isset($rates[$from])) {
			throw new InvalidArgumentException(sprintf("The currency '%' is not valid", $from));
		}

		$base  = $rates[$from];

		if ($base != 1.00) {
			foreach ($rates as $c => $r) {
				$rates[$c] = ($r / $base);
			}
		}

		return $rates;
	}

}