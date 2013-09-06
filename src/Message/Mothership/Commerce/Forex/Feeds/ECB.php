<?php

namespace Message\Mothershipo\Commerce\Forex\Feeds;

class ECB implements FeedInterface {

	protected $_url = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

	public function fetch()
	{
		$rates = array(
			'EUR' => 1.00
		);

		if ($xml = @simplexml_load_file($this->_url)) {
			foreach ($xml->Cube->Cube->Cube as $item) {
				$currency = $rate = null;

				foreach ($item->attributes() as $key => $val) {
					$$key = (string) $val;
				}

				$rates[$currency] = $rate;
			}

			unset($xml, $item, $key, $val);
		}
		
		foreach ($rates as $key => $val) {
            $this->_rates[$key] = ((1 / $rates[FXRate::BASE_CURRENCY]) * $rates[$key]);
			ksort($this->_rates);
        }
	}

}