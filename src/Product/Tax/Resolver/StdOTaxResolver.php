<?php

namespace Message\Mothership\Commerce\Product\Tax\Resolver;

use Message\Mothership\Commerce\Product\Type\ProductTypeInterface;
use Message\Mothership\Commerce\Address\Address;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRateCollection;
use Message\Mothership\Commerce\Product\Tax\Rate\TaxRate;

/**
 * {@inheritDoc}
 */
class StdOTaxResolver implements TaxResolverInterface
{
	const DEFAULT_REGION      = 'default';
	const DEFAULT_PRODUCT_TAX = 'product';
	const PRODUCT_TAX_APPEND  = 'Tax';

	private $_data;

	public function __construct($data)
	{
		$this->_data = $data;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProductTaxRates(ProductTypeInterface $productType, Address $address)
	{
		// setup defaults etc
		$data = $this->_data;
		$type    = $productType->getName();
		$country = strtolower($address->countryID);
		$region  = self::DEFAULT_REGION;

		if (isset($address->stateID)) {
			$region = strtolower($address->stateID);
		}

		// validation
		if (!isset($data->{$country})) {
			throw new \LogicException("Could not find given country tax configuration for country `$country`, make sure these are set in taxes config file");
		}

		if (!property_exists($data->{$country}, $region)) {
			throw new \LogicException("Could not find given region tax configuration for region `$address->stateID`, no default set. Make sure these are set in taxes config file");
		}

		// create tax collection
		$taxes = [];
		if (isset($data->{$country}->{$region}->{$type . self::PRODUCT_TAX_APPEND})){
			$taxes = $data->{$country}->{$region}->{$type . self::PRODUCT_TAX_APPEND};
		} else if (isset($data->{$country}->{$region}->{self::DEFAULT_PRODUCT_TAX . self::PRODUCT_TAX_APPEND})) {
			// only default if property is undefind
			if (!property_exists($data->{$country}->{$region}, $type . self::PRODUCT_TAX_APPEND)) {
				$type = self::DEFAULT_PRODUCT_TAX;
				$taxes = $data->{$country}->{$region}->{self::DEFAULT_PRODUCT_TAX . self::PRODUCT_TAX_APPEND};
			}
		}

		$taxRates = new TaxRateCollection();
		foreach ($taxes as $rate) {
			$taxRates->add(new TaxRate($rate->rate, $rate->name, implode('.', [$country, $region, $type])));
		}

		return $taxRates;
	}
}