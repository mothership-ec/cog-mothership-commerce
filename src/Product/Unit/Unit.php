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

	private $_product;

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

	public function __set($name, $val)
	{
		if ($name === 'product') {
			$this->setProduct($val);
		} else {
			$this->{$name} = $val;
		}
	}

	public function __get($name)
	{
		if ($name === 'product') {
			return $this->getProduct();
		}

		return $this->{$name};
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
		$currencyID = $currencyID ?: $this->_defaultCurrency;

		return $this->price[$type]->getPrice($currencyID, $this->_locale);
	}

	public function setPrice($price, $type = 'retail', $currencyID = 'GBP')
	{
		if (empty($this->price[$type])) {
			$this->price[$type] = new Pricing($this->_locale);
		}

		$this->price[$type]->setPrice($currencyID, (float) $price, $this->_locale);
	}

	public function getNetPrice($type = 'retail', $currencyID = 'GBP')
	{
		$product = $this->getProduct();

		return $product->getTaxStrategy()->getNetPrice(
			$this->getPrice($type, $currencyID),
			$product->getTaxRates()
		);
	}

	/**
	 * {@inheritDocs}
	 */
	public function hasPrice($type, $currencyID)
	{
		// Due to how the loader works, the closest way to tell wheather or not this entity has a price
		// is to compare it to the product. This isn't 100% as if this has a price set identical to the product
		// price then it will return false, however for most cases this false result is OK.
		return $this->price[$type]->hasPrice($currencyID)
				&& $this->getPrice($type, $currencyID) !== $this->getProduct()->getPrice($type, $currencyID);
	}

	public function getGrossPrice($type = 'retail', $currencyID = 'GBP')
	{
		$product = $this->getProduct();

		return $product->getTaxStrategy()->getGrossPrice(
			$this->getPrice($type, $currencyID),
			$product->getTaxRates()
		);
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

	public function getStockArray()
	{
		return $this->stock;
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

	/**
	 * Gets the unit's product
	 * @return Message\Mothership\Commerce\Product\Product The unit's
	 */

	public function getProduct()
	{
		return $this->_product;
	}

    /**
     * Sets the value of product.
     *
     * @param mixed $product the product
     *
     * @return self
     */
    public function setProduct($product)
    {
        $this->_product = $product;

        return $this;
    }

    /**
     * Gets the value of sku.
     *
     * @return mixed
     */
    public function getSKU()
    {
        return $this->sku;
    }

    /**
     * Sets the value of sku.
     *
     * @param mixed $sku the sku
     *
     * @return self
     */
    public function setSKU($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Gets the value of options.
     *
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Gets the value of visible.
     *
     * @return mixed
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Sets the value of visible.
     *
     * @param boolean $visible true if visible
     *
     * @return self
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }
}