<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\Authorship;
use Message\Mothership\Commerce\Product\Price\Pricing;
use Message\Mothership\Commerce\Product\Stock\Location\Location;
use Message\Mothership\Commerce\Product\Price\PricedInterface;

class Unit implements PricedInterface
{
	const DEFAULT_STOCK_LEVEL = 0;

	public $id;
	public $price;
	public $sku;
	public $barcode;
	public $visible;
	public $authorship;
	public $supplierRef;
	public $weight;
	public $revisionID;

	public $stock = array(

	);

	public $options = array(

	);

	public $product;

	protected $_locale;
	protected $_defaultCurrency;

	public function __construct(Locale $locale, array $priceTypes, $defaultCurrency)
	{
		$this->_defaultCurrency = $defaultCurrency;
		$this->authorship = new Authorship;
		$this->_locale = $locale;
		foreach ($priceTypes as $type) {
			$this->price[$type] = new Pricing($locale);
		}

	}

	public function __clone() {
		foreach ($this->price as $name => $pricing) {
			$this->price[$name] = clone $pricing;
		}
    }

	public function setOption($type, $value)
	{
		$this->options[$type] = $value;
	}

	public function getOptionString()
	{
		$options = implode(', ', $this->options);

		return ucfirst($options);
	}

	public function getPrices()
	{
		return $this->price;
	}

	public function getPrice($type = 'retail', $currencyID = null)
	{
		$currencyID = $currencyID?:$this->_defaultCurrency;

		return $this->price[$type]->getPrice($currencyID, $this->_locale);
	}

	public function setPrice($price, $type = 'retail', $currencyID = 'GBP')
	{
		$this->price[$type]->setPrice($currencyID, $price, $this->_locale);
	}

	/**
	 * Returns whether unit is out of stock in all locations
	 *
	 * @return boolean
	 */
	public function isOutOfStock()
	{
		return array_sum($this->stock) == 0;
	}

	public function getStockForLocation(Location $location)
	{
		return (isset($this->stock[$location->name]) ? $this->stock[$location->name] : self::DEFAULT_STOCK_LEVEL);
	}

	public function setStockForLocation($value, Location $location)
	{
		$this->stock[$location->name] = (int) $value;
		return $this;
	}

	public function getOption($type)
	{
		if (!isset($this->options[$type])) {
			throw new \InvalidArgumentException(sprintf('Option %s doesn\'t exist on unitID %i', $type, $this->id));
		}

		return $this->options[$type];
	}

	public function hasOption($type)
	{
		return isset($this->options[$type]);
	}
}