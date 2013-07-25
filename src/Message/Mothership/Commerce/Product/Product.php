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

	public function __construct(Locale $locale, array $entities = array(), array $priceTypes = array())
	{
		$this->authorship = new Authorship;
		$this->priceTypes = $priceTypes;
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

	//GET COLLECTIONS (SEE OPTS)
	public function __call($property, $arg) {
		$property = str_replace('get', '', $property);
		$property = strtolower(substr($property,0,1) ) . substr($property,1);
		$opts = array(
			'images',
			'ranges',
		);
		if (in_array($property, $opts)) {
			$func = '_load' . ucfirst($property);
			if (method_exists($this, $func)) {
				$this->{$func}();
			} else {
				$this->_loadAttribute(substr($property, 0, strlen($property) -1));
			}
			return $this->{'_' . $property};
		} else {
			throw new \Exception($property . ' cannot be called from Product with __call()');
		}
	}


	public function __wakeup() {
		$this->_db = new DBquery;
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
		return $this->getUnits(true, true);
	}

	public function getVisibleUnits()
	{
		return $this->getUnits(true, false);
	}


	public function getUnit($unitID)
	{
		$units = $this->getUnits(true, true);

		try {
			return $units->get($unitID);
		} catch(\Exception $e) {
			return false;
		}

		return false;
	}

	public function filterByColour(array $colours) {
		foreach($this->getUnits() as $unit) {
			if (!in_array($unit->colour_id, $colours)) {
				unset($this->_units[$unit->unit_id]);
			}
		}
	}


	//GET PRODUCT PRICE FOR SPECIFIC LOCALE
	public function getPrice($locale = NULL, $type = 'retail') {
		if (empty($localeID)) {
			$localeID = $this->_locale->getCurrencyID();
		}
		$this->_loadPrices();
		if (isset($this->_prices[$type][$localeID])) {
			return $this->_prices[$type][$localeID];
		}
		return false;
	}


	//IF PRODUCT HAS VARYING PRICE, RETURN LOWEST
	public function getPriceFrom($localeID = NULL, $type = 'retail') {
		if (empty($localeID)) {
			$localeID = $this->_locale->getCurrencyID();
		}
		$price = array();

		foreach ($this->getUnits() as $unit) {
			if(!isset($unit->price[$type])) {
				$unit->price[$type] = array();
			}

			if(!isset($unit->price[$type][$localeID])) {
				$unit->price[$type][$localeID] = false;
			}

			$price[$unit->price[$type][$localeID]] = true;
		}
		ksort($price);
		if (count($price) < 2) {
			return false;
		}
		//dump($price);
		return key($price);
	}

	public function getPriceByColour($colourID, $localeID = NULL, $type = 'retail', &$from = false) {
		if (empty($localeID)) {
			$localeID = $this->_locale->getCurrencyID();
		}
		$prices = array();
		foreach($this->getVisibleUnits() as $unit) {
			if ($unit->colour_id == $colourID) {
				$prices[] = $unit->getPrice(NULL, $type);
			}
		}

		if(!count($prices)) {
			$from = false;
			return $this->getPrice($localeID, $type);
		}

		if(count($prices) >= 1 && count(array_unique($prices)) == 1) {
			$from = false;
		} else {
			$from = true;
		}

		// This returns the lowest price (if there are a range of prices)
		sort($prices);

		return $prices[0];
	}


	//RETURN A NAME WITH BRAND NAME
	public function getFullName() {
		return $this->brandName.', '.$this->displayName;
	}


	//RETURN A UNIQUE DEFAULT PRODUCT NAME
	public function getDefaultName() {
		return $this->productName
		     . ' (V' . $this->versionID . ', '
			 . $this->productYear . ')';
	}

	public function isAvailableInSize(Size $size) {

		$return = false;

		foreach($this->getUnits() as $unit) {

			if(in_array($unit->size_id, $size->getAllSizes())) {
				$return = true;
				break;
			}

		}

		return $return;

	}

	public function hasRRP($colourID = false) {
		if($colourID) {
			$rrp 	= $this->getPriceByColour($colourID, NULL, 'rrp');
			$retail = $this->getPriceByColour($colourID, NULL, 'retail');
			return ($rrp > $retail);
		}

		if(!isset($low)) $low = 0;

		return (isset($this->rrp) && $low > $this->price);
	}

	public function getRrpRange($colourID = false, &$from = false) {
		$from   = false;
		$low 	= $this->rrp;
		$high 	= $this->rrp;
		foreach($this->getUnits() as $unit) {
			if ($unit->colour_id == $colourID) {
				if($unit->price['rrp'] < $low) {
					$low = $unit->price['rrp'];
				}
				if($unit->price['rrp'] > $high) {
					$high = $unit->price['rrp'];
				}
			}
		}

		if($low != $high) {
			$from = true;
		}

		return array($low, $high);
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