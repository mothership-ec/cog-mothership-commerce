<?php

namespace Message\Mothership\Commerce\Forex;

use Exception;
use Feeds\FeedInterface;

/**
 * Provides foreign exchange currency conversions.
 *
 * @author Laurence Roberts <lsjroberts@gmail.com>
 */
class Forex {

	protected $_query;
	protected $_currencies;
	protected $_baseCurrency;

	protected $_rates;

	/**
	 * Constructor.
	 * 
	 * @param Message\Cog\DB\Query $query
	 * @param string               $baseCurrency Base currency to do conversions against.
	 * @param array                $currencies   List of currencies that can be used in conversions.
	 */
	public function __construct($query, $baseCurrency, array $currencies)
	{
		$this->_query           = $query;
		$this->_currencies      = $currencies;
		$this->_baseCurrency    = $baseCurrency;
	}

	/**
	 * Create a converter, and if possible return the converted value.
	 * 
	 * @param  float|null       $amount Amount to convert
	 * @param  string|null      $to     Currency to convert to
	 * @param  string|null      $from   Currency to convert from
	 * 
	 * @return float|Converter  Converter instance if not all values provided for an instant conversion
	 */
	public function convert($amount = null, $to = null, $from = null)
	{
		if ($from == null) {
			$from = $this->_baseCurrency;
		}

		$converter = new Converter($this->getRates(), $amount, $from, $to);

		try {
			return $converter->get();
		}
		catch (Exception $e) {
			return $converter;
		}
	}

	/**
	 * Get the currency rates.
	 * 
	 * @return array
	 */
	public function getRates()
	{
		if ($this->_rates == null) {
			$this->_rates = array();

			$results = $this->_query->run('
				SELECT
					currency,
					rate
				FROM
					forex_rates
			');

			foreach ($results as $row) {
				$this->_rates[$row->currency] = $row->rate;
			}
		}

		return $this->_rates;
	}

}