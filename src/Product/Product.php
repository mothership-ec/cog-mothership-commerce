<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\ValueObject\Authorship;
use Message\Cog\Localisation\Locale;
use Message\Mothership\Commerce\Product\Type\ProductTypeInterface as ProductType;
use Message\Mothership\Commerce\Product\Tax\Strategy\TaxStrategyInterface;

class Product implements Price\PricedInterface
{
	public $id;
	public $catalogueID;
	public $brand;

	public $authorship;

	public $name;
	public $taxRate;
	public $taxStrategy;
	public $supplierRef;
	public $weight;

	public $displayName;
	public $sortName;
	public $description;
	public $category;
	public $shortDescription;
	public $notes;

	public $type;
	public $tags = array();


	public $exportDescription;
	public $exportValue;
	public $exportManufactureCountryID;

	public $priceTypes;

	protected $_prices;
	protected $_details;
	protected $_units;
	protected $_images;
	protected $_locale;
	protected $_taxes;

	protected $_taxStrategy;

	protected $_defaultCurrency;

	/**
	 * Initiate the object and set some basic properties up
	 *
	 * @param Locale $locale     	Current locale instance
	 * @param array  $priceTypes 	array of price types
	 * @param array  $taxStrategy   the tax strategy to use to resolve the correct price
	 */
	public function __construct(Locale $locale, array $priceTypes = array(), $defaultCurrency, TaxStrategyInterface $taxStrategy)
	{
		$this->authorship  = new Authorship;
		$this->priceTypes  = $priceTypes;
		$this->_locale     = $locale;
		$this->_taxStrategy = $taxStrategy;
		$this->_defaultCurrency = $defaultCurrency;

		$this->_units      = new Unit\Collection;
		$this->_images     = new Image\Collection;
		$this->_details    = new Type\DetailCollection;
		$this->_prices     = new Price\PriceCollection($priceTypes);
		$this->_taxes      = new Tax\Rate\TaxRateCollection;
	}

	/**
	 * return units and give options as to which ones to display
	 *
	 * @param  boolean $showOutOfStock Bool to load out of stock units
	 * @param  boolean $showInvisible  Bool to load invisble units
	 *
	 * @return array                   array of Unit objects
	 */
	public function getUnits($showOutOfStock = true, $showInvisible = false)
	{
		return $this->_units->getByCriteria($showOutOfStock, $showInvisible);
	}

	/**
	 * Return an array of all units for this product, including out of stock and
	 * units set to invisble.
	 *
	 * @return array 		array of Unit objects
	 */
	public function getAllUnits()
	{
		return $this->getUnits(true, true);
	}

	public function getVisibleUnits()
	{
		return $this->getUnits(true, false);
	}

	public function getUnitCollection()
	{
		return $this->_units;
	}

	/**
	 * Get a specfic unit by the unitID
	 *
	 * @param  int 		$unitID 	The unitID to load the Unit for
	 *
	 * @return Unit|false       	Loaded unit or false if not found
	 */
	public function getUnit($unitID)
	{
		try {
			return $this->_units->get($unitID);
		} catch (\Exception $e) {
			return false;
		}
	}

	public function getPrices()
	{
		return $this->_prices;
	}

	/**
	 * Get the current price of price type based on the current locale and
	 * given currencyID
	 *
	 * @param  string $type       Price type to load
	 * @param  string $currencyID CurrencyID to load
	 *
	 * @return string             Loaded price
	 */
	public function getPrice($type = 'retail', $currencyID = null)
	{
		$currencyID = $currencyID ?: $this->_defaultCurrency;

		return $this->getPrices()[$type]->getPrice($currencyID, $this->_locale);
	}

	/**
	 * Get the lowest price for this product by checking the unit-level price
	 * overrides.
	 *
	 * @param  string $type       Type of price to check
	 * @param  string $currencyID CurrencyID of price to check
	 *
	 * @return string|false       Lowest price or false if $prices is empty
	 */
	public function getPriceFrom($type = 'retail', $currencyID = null)
	{
		$currencyID = $currencyID ?: $this->_defaultCurrency;

		$basePrice = $this->getPrice($type, $currencyID);
		$prices    = array();

		foreach ($this->getVisibleUnits() as $unit) {
			if ($unit->getPrice($type, $currencyID) < $basePrice) {
				$prices[$unit->getPrice($type, $currencyID)] = $unit->getPrice($type, $currencyID);
			}
		}
		// Sort the array with lowest value at the top
		ksort($prices);
		// get the lowest value
		return $prices ? array_shift($prices) : $basePrice;
	}

	/**
	 * Get the net price
	 */
	public function getNetPrice($type = 'retail', $currencyID = null)
	{
		$currencyID = $currencyID ?: $this->_defaultCurrency;

		return $this->_taxStrategy->getNetPrice($this->getPrice($type, $currencyID), $this->getTaxRates());
	}

	/**
	 * Get the lowest possible net price
	 */
	public function getNetPriceFrom($type = 'retail', $currencyID = null)
	{
		$currencyID = $currencyID ?: $this->_defaultCurrency;

		return $this->_taxStrategy->getNetPrice($this->getPriceFrom($type, $currencyID), $this->getTaxRates());
	}

	/**
	 * Get the gross price
	 */
	public function getGrossPrice($type = 'retail', $currencyID = 'GBP')
	{
		return $this->_taxStrategy->getGrossPrice($this->getPrice($type, $currencyID), $this->getTaxRates());
	}

	/**
	 * Get the lowest possible gross price
	 */
	public function getGrossPriceFrom($type = 'retail', $currencyID = 'GBP')
	{
		return $this->_taxStrategy->getGrossPrice($this->getPriceFrom($type, $currencyID), $this->getTaxRates());
	}

	/**
	 * Check whether this product has variable pricing in a specific type &
	 * currency ID.
	 *
	 * @todo Allow $options to be passed, only checking units which match that
	 *       options criteria
	 *
	 * @param  string     $type       Type of price to check
	 * @param  string     $currencyID Currency ID
	 * @param  array|null $options    Array of options criteria for units to check
	 *
	 * @return boolean                Result of checkPunit
	 */
	public function hasVariablePricing($type = 'retail', $currencyID = null, array $options = null)
	{
		$currencyID = $currencyID ?: $this->_defaultCurrency;

		$units = $this->getVisibleUnits();
		if (sizeof($units) <= 1) {
			return false;
		}

		$basePrice = null;
		foreach ($units as $unit) {
			$valid = true;
			if($options) {
				// validate for option constraints
				foreach($options as $option => $val) {
					if($unit->getOption($option) !== $val) {
						$valid = false;
						break;
					}
				}
				// skip unit if not valid
				if(!$valid) {
					continue;
				}
			}

			$unitPrice = $unit->getPrice($type, $currencyID);
			if($basePrice === null) {
				$basePrice = $unitPrice;
			}else if ($unit->getPrice($type, $currencyID) !== $basePrice) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns full name consisting of brand (if set) and display name,
	 * separated by a comma.
	 *
	 * @return string full name
	 */
	public function getFullName()
	{
		return ($this->brand ? $this->brand . ', ' : '') .$this->displayName;
	}

	/**
	 * Return the internal product name (not the display name)
	 *
	 * @return string
	 */
	public function getDefaultName()
	{
		return $this->name;
	}

	/**
	 * Get one image of a specific type for this product.
	 *
	 * An associative array of options criteria can also be passed. If this is
	 * set, only images matching the option criteria will be returned.
	 *
	 * If multiple images are found that match this criteria, only the first
	 * will be returned.
	 *
	 * @param  string|null $type    The image type to get images for
	 * @param  array|null  $options Associative array of options, or null for all
	 *
	 * @return Image|null           Image matching the criteria, or null if none
	 *                              found
	 */
	public function getImage($type = 'default', array $options = null)
	{
		$images = $this->getImages($type, $options);

		return count($images) > 0 ? array_shift($images) : null;
	}

	/**
	 * Get images of a specific type for this product.
	 *
	 * An associative array of options criteria can also be passed. If this is
	 * set, only images matching the option criteria will be returned.
	 *
	 * @param  string|null $type    The image type to get images for
	 * @param  array|null  $options Associative array of options, or null for all
	 *
	 * @return array                Array of images matching the criteria
	 */
	public function getImages($type = null, array $options = null)
	{
		if (null !== $type) {
			return $this->_images->getByType($type, $options);
		}

		return $this->_images->all();
	}

	/**
	 * Get the image most appropriate for a particular unit.
	 *
	 * This checks for an image with all of the options set to all of the
	 * options of this unit first, then it iterates over all options and tries to
	 * find an image with at least one matching option. If this doesn't return
	 * an image, it will just return the image of this type for the product with
	 * no option criteria.
	 *
	 * @todo Make this somehow prefer an image if it matches MORE option criteria
	 *       than another (i.e. unit is Red/Small/Velvet), it will prefer an
	 *       image for Red/Velvet than just Red.
	 *
	 * @param  Unit\Unit $unit The unit to get an image for
	 * @param  string    $type The image type to get
	 *
	 * @return Image|null
	 */
	public function getUnitImage(Unit\Unit $unit, $type = 'default')
	{
		if ($image = $this->getImage($type, $unit->options)) {
			return $image;
		}

		foreach ($unit->options as $name => $value) {
			if ($image = $this->getImage($type, array($name => $value))) {
				return $image;
			}
		}

		return $this->getImage($type);
	}

	/**
	 * Check if an image of a specific type exists.
	 *
	 * An associative array of options criteria can also be passed. If this is
	 * set, only images matching the option criteria will be returned.
	 *
	 * @param  string     $type    The image type to get images for
	 * @param  array|null $options Associative array of options, or null for all
	 *
	 * @return boolean
	 */
	public function hasImage($type = 'default', array $options = null)
	{
		return $this->getImage($type, $options);
	}

	/**
	 * Get the tax rates
	 * 
	 * @return TaxRateCollection The collection of tax rates
	 */
	public function getTaxRates()
	{
		return $this->_taxes;
	}

	/**
	 * Get the object type
	 * 
	 * @return ProductType The product's type
	 */
	public function getType()
	{
		return $this->type;
	}

	public function hasTag($tag)
	{
		return in_array($tag, $this->tags);
	}

	public function getDetails()
	{
		return $this->_details;
	}

	public function setDetails(Type\DetailCollection $details)
	{
		$this->_details = $details;
	}

	/**
	 * Gets the value of name.
	 *
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Sets the value of name.
	 *
	 * @param mixed $name the name 
	 *
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}
	/**
	 * Sets the tax strategy.
	 *
	 * @param mixed $_taxStrategy the tax manager
	 *
	 * @return self
	 */
	protected function setTaxStrategy($taxStrategy)
	{
		$this->_taxStrategy = $taxManager;

		return $this;
	}

	/**
	 * Sets the value of name.
	 *
	 * @param mixed $name the name 
	 *
	 * @return self
	 */
	public function setDisplayName($name)
	{
		$this->displayName = $name;

		return $this;
	}

	/**
	 * Gets the value of brand.
	 *
	 * @return mixed
	 */
	public function getBrand()
	{
		return $this->brand;
	}
	
	/**
	 * Sets the value of brand.
	 *
	 * @param mixed $brand the brand 
	 *
	 * @return self
	 */
	public function setBrand($brand)
	{
		$this->brand = $brand;

		return $this;
	}

	/**
	 * Gets the value of category.
	 *
	 * @return mixed
	 */
	public function getCategory()
	{
		return $this->category;
	}
	
	/**
	 * Sets the value of category.
	 *
	 * @param mixed $category the category 
	 *
	 * @return self
	 */
	public function setCategory($category)
	{
		$this->category = $category;

		return $this;
	}

	/**
	 * Gets the value of shortDescription.
	 *
	 * @return mixed
	 */
	public function getShortDescription()
	{
		return $this->shortDescription;
	}
	
	/**
	 * Sets the value of shortDescription.
	 *
	 * @param mixed $shortDescription the short description 
	 *
	 * @return self
	 */
	public function setShortDescription($shortDescription)
	{
		$this->shortDescription = $shortDescription;

		return $this;
	}

	/**
	 * Gets the value of description.
	 *
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}
	
	/**
	 * Sets the value of description.
	 *
	 * @param mixed $description the description 
	 *
	 * @return self
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Adds a unit to the Unit Collection
	 * 
	 * @param Unit\Unit $unit Unit to add
	 */
	public function addUnit(Unit\Unit $unit)
	{
		$this->_units->add($unit);

		return $this;
	}
	
	/**
	 * Sets the value of type.
	 *
	 * @param mixed $type the type 
	 *
	 * @return self
	 */
	public function setType(ProductType $type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Gets the value of displayName.
	 *
	 * @return mixed
	 */
	public function getDisplayName()
	{
		return $this->displayName;
	}

	/**
	 * Gets the tax strategy in use on this product
	 * 
	 * @return Tax\Strategy\TaxStrategyInterface The strategy in use
	 */
	public function getTaxStrategy()
	{
		return $this->_taxStrategy;
	}
}