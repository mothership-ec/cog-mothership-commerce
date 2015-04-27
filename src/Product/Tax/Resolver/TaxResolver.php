<?php

namespace Message\Mothership\Commerce\Product\Tax\Resolver;

use Message\Mothership\Commerce\Product\Type\ProductTypeInterface;
use Message\Mothership\Commerce\Address\Address;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;
use Message\Mothership\Commerce\Product\Tax\Exception;

/**
 * {@inheritDoc}
 *
 * This class looks at tax.yml to resolve the tax
 */
class TaxResolver implements TaxResolverInterface
{
	const DEFAULT_REGION       = 'default';
	const DEFAULT_SHIPPING_TAX = 'shipping';
	const DEFAULT_COUNTRY      = 'default';
	const DEFAULT_PRODUCT_TAX  = 'product';
	const PRODUCT_TAX_APPEND   = 'Tax';

	private $_data;

	public function __construct($data)
	{
		$this->_data = $data;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTaxRates($name, Address $address)
	{
		// setup defaults etc
		$data    = $this->_data;
		$type    = $name;
		$country = strtolower($address->countryID);
		$region  = strtolower($address->stateID);

		if (!$region) {
			$region = self::DEFAULT_REGION;
		}

		// validation
		if (!isset($data->{$country})) {
			if (!isset($data->{self::DEFAULT_COUNTRY})){
				throw new Exception\TaxRateNotFoundException("Could not find given country tax configuration for country `$country` and no default set, make sure these are set in taxes config file");
			}

			$country = self::DEFAULT_COUNTRY;
		}

		if (!property_exists($data->{$country}, $region)) {
			if (!property_exists($data->{$country}, self::DEFAULT_REGION)) {
				throw new Exception\TaxRateNotFoundException("Could not find given region tax configuration for region `$address->stateID`, no default set. Make sure these are set in taxes config file");
			}

			$region = self::DEFAULT_REGION;
		}

		// create tax collection
		$taxes = [];
		if (isset($data->{$country}->{$region}->{$type . self::PRODUCT_TAX_APPEND})){
			$taxes = $data->{$country}->{$region}->{$type . self::PRODUCT_TAX_APPEND};
		} else if (isset($data->{$country}->{$region}->{self::DEFAULT_PRODUCT_TAX . self::PRODUCT_TAX_APPEND})) {
			// only default if property is undefind
			if (!property_exists($data->{$country}->{$region}, $type . self::PRODUCT_TAX_APPEND)) {
				$type  = self::DEFAULT_PRODUCT_TAX;
				$taxes = $data->{$country}->{$region}->{self::DEFAULT_PRODUCT_TAX . self::PRODUCT_TAX_APPEND};
			}
		}

		$taxRates = new TaxRateCollection();
		foreach ($taxes as $rate) {
			try {
				$taxRates->add(new TaxRate($rate->rate, $rate->name, implode('.', [$country, $region, $type, strtolower($rate->name)])));
			} catch (\Exception $e) {
				throw new \LogicException("Could not set TaxRate on collection, ensure no duplicate taxes declared!"); 
			}
		}

		return $taxRates;
	}
}