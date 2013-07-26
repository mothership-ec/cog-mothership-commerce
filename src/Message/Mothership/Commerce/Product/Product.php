<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\Service\Container;
use Message\Cog\ValueObject\Authorship;
use Message\Cog\Localisation\Locale;

class Product
{
	public $id;
	public $catalogueID;
	public $year;

	public $authorship;

	public $brandID;
	public $name;
	public $taxRate;
	public $supplierRef;
	public $weightGrams;

	public $displayName;
	public $season;
	public $description;
	public $category;
	public $fabric;
	public $features;
	public $careInstructions;
	public $shortDescription;
	public $sizing;
	public $notes;

	public $price = array();

	public $units;
	public $images   = array();
	public $tags     = array();

	public $exportDescription;
	public $exportValue;
	public $exportManufactureCountryID;

	public $unstackedExportDescription;
	public $unstackedExportValue;
	public $unstackedExportManufactureCountryID;

	public $priceTypes;

	protected $_entities = array();
	protected $_locale;

	public function __construct(Locale $locale, array $entities = array(), array $priceTypes = array())
	{
		$this->authorship = new Authorship;
		$this->priceTypes = $priceTypes;
		$this->_locale    = $locale;

		foreach ($entities as $name => $loader) {
			$this->addEntity($name, $loader);
		}

		foreach ($priceTypes as $type) {
			$this->price[$type] = new Pricing($locale);
		}

	}

	/**
	 * Add an entity to this product.
	 *
	 * @param string                 $name   Entity name
	 * @param Entity\LoaderInterface $loader Entity loader
	 *
	 * @throws \InvalidArgumentException If an entity with the given name already exists
	 */
	public function addEntity($name, Unit\LoaderInterface $loader)
	{
		if (array_key_exists($name, $this->_entities)) {
			throw new \InvalidArgumentException(sprintf('Order entity already exists with name `%s`', $name));
		}

		$this->_entities[$name] = new Unit\Collection($this, $loader);
	}

	public function getUnits($showOutOfStock = true, $showInvisible = false) {
		$this->_entities['unit']->load($this, $showOutOfStock, $showInvisible);
		return $this->_entities['unit'];
	}

	public function getImage($typeID, $colourID = 0) {
		$this->getImages();
		if (isset($this->_images[$typeID][$colourID])) {
			return $this->_images[$typeID][$colourID];
		} elseif (isset($this->_images[$typeID][0])) { // 0 is all colours
			return $this->_images[$typeID][0];
		}
		return false;
	}


	public function hasImage($typeID, $colourID = 0) {
		return (!empty($this->getImage($typeID, $colourID)->image_location));
	}

	public function getColour($id)
	{
		$this->getColours();
		return (isset($this->_colours[$id])) ? $this->_colours[$id] : false;
	}


	public function getAllUnits()
	{
		return $this->getUnits(true, true)->all();
	}

	public function getVisibleUnits()
	{
		return $this->getUnits(true, false);
	}


	public function getUnit($unitID)
	{
		try {
			$units = $this->getUnits(true, true);

			return $units->get($unitID);
		} catch(\Exception $e) {
			return false;
		}
	}

	public function getPrice($type = 'retail', $currencyID = 'GBP') {
		return $this->price[$type]->getPrice($currencyID, $this->_locale);
	}

	/**
	 * Check unit level prices and see whether there is any unit prices which are
	 * less that the product price
	 *
	 * @param  string $type       Type of price to check
	 * @param  string $currencyID CurrencyID of price to check
	 *
	 * @return string|false       Lowest price or false if $prices is empty
	 */
	public function getPriceFrom($type = 'retail', $currencyID = 'GBP') {
		$prices = array();
		foreach ($this->getAllUnits() as $unit) {
			if ($unit->getPrice($type, $currencyID) < $this->getPrice($type, $currencyID)) {
				$prices[$unit->getPrice($type, $currencyID)] = $unit->getPrice($type, $currencyID);
			}
		}
		// Sort the array with lowest value at the top
		ksort($prices);
		// get the lowest value
		return $prices ? array_shift($prices) : false;
	}


	public function getFullName() {
		return $this->brandName.', '.$this->displayName;
	}

	public function getDefaultName() {
		return $this->name;
	}

/********************************************************************
*
*   SAVE IMAGES
*
********************************************************************/


	public function setImage($imageTypeID, FileUpload $upload, $colourID = '0', $borderFlag = false) {
		$size   = isset($this->_imageSizes[$imageTypeID]) ? $this->_imageSizes[$imageTypeID] : NULL;
		$prefix = SYSTEM_PATH . 'public_html/' . AREA;
		$dir    = $this->_getImageDir();
		$file   = implode('_', array(
					Locale::DEFAULT_LOCALE_ID,
					$this->productID,
					$this->versionID,
					$imageTypeID,
					$colourID
			)
		);

		//$borderFlag will add a flag to the image name
		//we are using this in the Print listings for
		//products which a white background to add a css border
		if($borderFlag) {
			$file .= $borderFlag;
		}

		if ($upload->getUpload()) {
			$img = ImageSize::init($upload->getUpload(), $prefix . $dir . $file);
			$img->setQuality(90);

			if ($path = $img->saveResized($size, $size)) {
				$img = new Image;
				$img->catalogue_id   = $this->catalogueID;
				$img->image_location = str_replace($prefix, '', $path);
				$img->locale_id      = $this->_locale->getId();
				$img->image_id       = NULL;
				$img->image_type_id  = $imageTypeID;
				$img->colour_id  	 = $colourID;
				$this->_images[]     = $img;
			}
		}
	}


	public function setImageWithoutUpload($imageTypeID, $path, $colourID = '0') {
		$img = new Image;
		$img->catalogue_id   = $this->catalogueID;
		$img->image_location = $path;
		$img->locale_id      = $this->_locale->getId();
		$img->image_id       = NULL;
		$img->image_type_id  = $imageTypeID;
		$img->colour_id  	 = $colourID;
		$this->_images[]     = $img;
	}


	protected function _getImageDir() {
		$path = '/images/products/' . $this->productID;
		if (!file_exists(SYSTEM_PATH . 'public_html/'. AREA . $path)) {
			mkdir(SYSTEM_PATH . 'public_html/' . AREA . $path);
		}
		return $path . '/';
	}
}