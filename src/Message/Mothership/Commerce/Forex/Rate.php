<?php

namespace Message\Mothership\Commerce\Forex;

class Rate {

	protected $_feed;
	protected $_currencies;
	protected $_baseCurrency;

	protected $_rates;

	public function __construct(FeedInterface $feed, $baseCurrency, array $currencies)
	{
		$this->_feed            = $feed;
		$this->_currencies      = $currencies;
		$this->_baseCurrency    = $baseCurrency;
	}

	/**
	 * $rate->convert(12.50, 'USD', 'GBP')
	 *
	 * or?
	 * $rate->from('GBP')->to('USD')->convert(12.50)
	 * 
	 * @param  [type] $amount [description]
	 * @return [type]         [description]
	 */
	public function convert($amount, $to, $from = null)
	{
		$rate = $this->getRate($to, $from);
	}

	public function getRate($currency, $from = null)
	{
		$rates = $this->getRates($from);

		if (!isset($rates[$currency])) {
			throw new Exception(sprintf("The currency '%' is not valid", $currency));
		}
	}

	public function getRates($from = null)
	{
		$rates = null;

		if ($from == null) {
			if ($this->_rates == null) {
				$this->_rates = $this->convertRates($this->_baseCurrency);
			}
			$rates = $this->_rates;
		}
		else {
			$rates = $this->convertRates($from);
		}

		return $rates;
	}

	public function convertRates($baseCurrency)
	{
		$rates = $this->_feed->getRates();

		$base = $rates[$this->_baseCurrency];

		if ($base != 1.00) {
			foreach ($rates as $c => $r) {
				$rates[$c] = 1 / ($r / $base);
			}
		}
	}

}