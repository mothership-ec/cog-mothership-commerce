<?php


class OrderTax
{
	
	//BASE COUNTRY FOR TAX RATES.  VATABLE COUNTRIES SHOULD USE THESE RATES UNLESS THEY HAVE THEIR OWN SET
	const DEFAULT_COUNTRY_ID = 'GB';
	
	
	//HOLD THE VAT RATES
	protected $_defaultRates;
	protected $_rates = array();
	
	
	//LOAD THE RATES AND STACK THEM FOR EACH COUNTRY IN THE TABLE
	public function __construct() {
		$this->_loadRates();
		$this->_setDefaultRates();
		$this->_stackRates();
	}
	
	
	//RETURN TAX RATES FOR A GIVEN COUNTRY, OR NULL IF WE DON'T HAVE ANY
	public function getRates($countryID) {
		if (isset($this->_rates[$countryID])) {
			return $this->_rates[$countryID]['rates'];
		}
		return NULL;
	}
	
	
	//RETURN AN ARRAY OF TAX RATES AND DIMENSIONS CODES
	//ALLOWS ACCESS TO DEFAULT RATES IF A COUNTRY ID IS NOT SET
	public function getRatesWithDimensonsCode($countryID = NULL) {
		if (is_null($countryID)) {
			$countryID = self::DEFAULT_COUNTRY_ID;
		}
		if (isset($this->_rates[$countryID])) {
			return $this->_rates[$countryID];
		}
		return NULL;
	}
	
	
	//FOR ANY COUNTRY WE HOLD VAT RATES FOR, STACK OVER THE DEFAULTS
	protected function _stackRates() {
		foreach ($this->_rates as $countryID => $rates) {
			foreach ($this->_defaultRates['rates'] as $code => $rate) {
				if (!isset($this->_rates[$countryID]['rates'][$code])) {
					$this->_rates[$countryID]['rates'][$code] = $rate;
				}
			}
			foreach ($this->_defaultRates['dim'] as $code => $dim) {
				if (!isset($this->_rates[$countryID]['dim'][$code])) {
					$this->_rates[$countryID]['dim'][$code] = $dim;
				}
			}
		}
	}
	
	
	//LOAD RATES FOR THE DEFAULT COUNTRY
	protected function _setDefaultRates() {
		if (isset($this->_rates[self::DEFAULT_COUNTRY_ID])) {
			$this->_defaultRates =  $this->_rates[self::DEFAULT_COUNTRY_ID];
		}
	}
	
	
	//LOAD ALL THE RATES FROM THE ORDER TAX TABLE
	protected function _loadRates() {
		$db = new DBquery;
		$query = 'SELECT country_id, tax_code, tax_rate, dimensions_code '
			   . 'FROM lkp_country_region '
			   . 'LEFT JOIN order_tax USING (country_id) '
			   . "WHERE vat = 'Y' ORDER BY country_id ASC";
		if ($db->query($query)) {
			while ($row = $db->row('OBJECT')) {
				if (!isset($this->_rates[$row->country_id])) {
					$this->_rates[$row->country_id] = array(
						'rates' => array(),
						'dim'   => array()
					);
				}
				if ($row->tax_code) {
					$this->_rates[$row->country_id]['rates'][$row->tax_code] = $row->tax_rate;
					if (is_numeric($row->dimensions_code)) {
						$this->_rates[$row->country_id]['dim'][$row->tax_code] = $row->dimensions_code;
					}
				}
			}
		}
	}
	
}



?>