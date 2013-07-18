<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\Service\Container;
use Message\Cog\ValueObject\Authorship;

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
	public $fabric;
	public $features;
	public $careInstructions;
	public $shortDescription;
	public $sizing;
	public $notes;

	public $price = array(
		'GBP' => array(
			'retail' => 0,
			'rrp'    => 0,
			'cost'   => 0,
		),
	);

	public $units;
	public $images   = array();
	public $tags     = array();

	public $exportDescription;
	public $exportValue;
	public $exportManufactureCountryID;

	public $unstackedExportDescription;
	public $unstackedExportValue;
	public $unstackedExportManufactureCountryID;

	protected $_entities = array();

	public function __construct(array $entities = array())
	{
		$this->authorship = new Authorship;
		foreach ($entities as $name => $loader) {
			$this->addEntity($name, $loader);
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
	public function addEntity($name, Entity\LoaderInterface $loader)
	{
		if (array_key_exists($name, $this->_entities)) {
			throw new \InvalidArgumentException(sprintf('Order entity already exists with name `%s`', $name));
		}

		$this->_entities[$name] = new Entity\Collection($this, $loader);
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
			throw new Exception($property . ' cannot be called from Product with __call()');
		}
	}


	public function __wakeup() {
		$this->_db = new DBquery;
	}


	public function getUnits($inStockOnly = true, $visibleOnly = true) {
		$this->_entities['unit']->load();;
		return $this->_entities['unit']->all();
		// $this->_loadUnits();
		// $units = array();

		// foreach ($this->_units as $unit) {
		// 	if (!$inStockOnly || $unit->getStock() > 0) {
		// 		if(!$visibleOnly || $unit->visible) {
		// 			$units[$unit->unit_id] = $unit;
		// 		}
		// 	}
		// }

		// return $units;
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

	public function getColour($id) {
		$this->getColours();
		return (isset($this->_colours[$id])) ? $this->_colours[$id] : false;
	}


	public function getAllUnits() {
		return $this->getUnits(false, false);
	}

	public function getVisibleUnits() {
		return $this->getUnits(false);
	}


	public function getUnit($unitID) {

		$this->getUnits();

		if (isset($this->_units[$unitID])) {
			return $this->_units[$unitID];
		}
		return false;
	}


	public function filterByVariant(array $variants) {
		foreach($this->getUnits() as $unit) {
			if (!in_array($unit->variant_id, $variants)) {
				unset($this->_units[$unit->unit_id]);
			}
		}
	}


	public function filterByColour(array $colours) {
		foreach($this->getUnits() as $unit) {
			if (!in_array($unit->colour_id, $colours)) {
				unset($this->_units[$unit->unit_id]);
			}
		}
	}


	//GET PRODUCT PRICE FOR SPECIFIC LOCALE
	public function getPrice($localeID = NULL, $type = 'retail') {
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
*   CREATE PRODUCT, VERSIONS, ETC
*
********************************************************************/

	public function save() {
		$required = array(
			'productName',
			'brandID',
			'categoryID',
			'taxCode',
			'productYear'
		);
		foreach ($required as $property) {
			if (is_null($this->{$property})) {
				throw new Exception('Error creating new product: missing required fields');
			}
		}
		if (is_null($this->productID)) {
			$db = new DBtransaction;
			$db->add('INSERT INTO catalogue_product (product_name, category_id, brand_id, tax_code, product_key, supplier_ref) '
				   . 'VALUES ('
				   . $db->escape($this->productName) . ','
				   . (int) $this->categoryID . ','
				   . (int) $this->brandID . ','
				   . $db->escape($this->taxCode) . ','
				   . $db->escape($this->productKey) . ','
				   . $db->escape($this->supplierRef) . ');');
			$db->add('SET @PRODUCT_ID = LAST_INSERT_ID();');
			$db->add('INSERT INTO catalogue (product_id, version_id, product_year) '
				   . 'VALUES (@PRODUCT_ID, 1, ' . $this->productYear . ');');
			$db->add('SET @CATALOGUE_ID = LAST_INSERT_ID();');
			$db->add('SELECT catalogue_id, product_id, version_id '
				   . 'FROM catalogue WHERE catalogue_id = @CATALOGUE_ID;');
			try {
				if ($db->run()) {
					foreach ($db->row() as $key => $val) {
						$this->{toCamelCaps($key)} = $val;
					}
					$this->_fb->addSuccess('Product created');
				} else {
					throw new Exception('Error creating new product:' . $db->error());
				}
			} catch (Exception $e) {
				throw new Exception('Error creating new product:' . $e->getMessage());
			}
		}
		return $this->catalogueID;
	}


	//CREATE A NEW VERSION OF THIS PRODUCT
	public function addVersion() {
		$query = 'INSERT INTO catalogue (product_id, version_id, product_year, date_created, date_updated) '
			   . 'SELECT product_id, max(version_id) + 1, ' . date('Y') . ', NOW(), NOW() '
			   . 'FROM catalogue '
			   . 'WHERE product_id = ' . $this->productID . ' '
			   . 'GROUP BY product_id';
		$this->_db->query($query);
		if ($id = $this->_db->id()) {
			$this->_loadProduct($this->productID, NULL);
			$this->_fb->addSuccess('New product version added for ' . $this->productName);
		} else {
			$this->_fb->addError('Unable to create new product version for ' . $this->productName);
		}
		return $id;
	}


	//ADD A NEW UNIT
	public function addUnit(ProductUnit $unit) {
		$this->_units[] = $unit;
	}



/********************************************************************
*
*   UPDATE PRODUCT
*
********************************************************************/

	public function __set($property, $value) {
		if (isset($this->{$property})) {
			$this->{$property} = $value;
		}
	}


	public function update($section = 'ALL') {
		$this->_trans = new DBtransaction;
		$this->_updateProduct();
		switch ($section) {
			case 'ATTRIBUTES':
				$this->_updateProductInfo();
				$this->_updateExportInfo();
				break;
			case 'IMAGES':
				$this->_updateProductImages();
				break;
			case 'UNITS':
				$this->_updateUnits();
				break;
			case 'STOCK':
				$this->_updateStock();
				break;
			case 'ALL':
				$this->_updateProductInfo();
				$this->_updateExportInfo();
				$this->_updateProductImages();
				$this->_updateUnits();
				$this->_updateStock();
				break;
			default:
		}
		try {
			$this->_trans->run();
		}
		catch(Exception $e) {
			$this->_fb->addError('There was a problem updating the product');
			return false;
		}
		if ($this->_trans->result()) {
			$this->_fb->addSuccess('Product updated');
		} else {
			$this->_fb->addError('There was a problem updating the product');
		}
	}


	public function updateProperty($property, $value) {
		switch ($property) {
			case 'ranges':
				$this->_setRanges($value);
				break;

			case 'cross_sell':
				$this->_setCrossSell($value);
				break;

			default:
				if ($this->{toCamelCaps($property)} != $value) {
					$this->{toCamelCaps($property)} = $value;
					$this->_updates[] = $property;
				}
		}
	}


	protected function _setRanges($csv) {
		$names = array_map('strtolower', array_map('trim', explode(',', $csv)));
		$this->_saveRanges($names);
		$ranges = $this->_getRangeList();
		$ids = array();
		foreach ($names as $name) {
			$ids[] = $ranges[$name];
		}
		$this->_setAttribute('range', $ids);
	}


	protected function _updateProduct() {
		$this->_trans->add('UPDATE catalogue SET '
			    . 'product_year = ' . $this->productYear . ','
			    . 'default_cross_sell = ' . (int) $this->defaultCrossSell . ','
			    . 'date_updated = NOW() '
				. 'WHERE catalogue_id = ' . $this->catalogueID
		);
		$this->_trans->add('UPDATE catalogue_product SET '
			    . 'category_id = ' . $this->categoryID . ','
			    . 'brand_id = ' . $this->brandID . ','
			    . 'supplier_ref = ' . $this->_trans->escape($this->supplierRef) . ','
				. 'product_name = ' . $this->_trans->escape($this->productName) . ','
				. 'product_key = ' . $this->_trans->escape($this->productKey) . ','
				. 'tax_code = ' . $this->_trans->escape($this->taxCode) . ','
				. 'bodypart_id = ' . ($this->bodypartID ? (int) $this->bodypartID : 'NULL') . ','
				. 'sizegroup_id = ' . ($this->sizegroupID ? (int) $this->sizegroupID : 'NULL') . ','
				. 'tax_code = ' . $this->_trans->escape($this->taxCode) . ','
				. 'weight = ' . $this->_trans->escape($this->weight) . ' '
				. 'WHERE product_id = ' . $this->productID
		);
	}


	protected function _updateProductInfo() {
		$fields = array(
			'display_name',
			'season',
			'description',
			'fabric',
			'features',
			'care_instructions',
			'price',
			'wholesale_price',
			'rrp',
			'cost_price',
			'sale_price',
			'short_description',
			'sizing',
			'order_id',
			'picking_description',
			'notes'
		);
		$inserts = array();
		foreach ($fields as $field) {
			if (in_array($field, $this->_updates)) {
				$inserts[] = $field . ' = ' . $this->_trans->escape($this->{toCamelCaps($field)});
			}
		}
		if ($inserts) {
			$this->_trans->add('INSERT IGNORE INTO catalogue_info (catalogue_id, locale_id) VALUES '
					. '(' . $this->catalogueID . ',' . $this->_trans->escape($this->_locale->getId()) . ')');
			$this->_trans->add('UPDATE catalogue_info SET ' . implode(',', $inserts) . ' '
					. 'WHERE catalogue_id = ' . $this->catalogueID . ' '
					. 'AND locale_id = ' . $this->_trans->escape($this->_locale->getId()));
		}
	}


	protected function _updateExportInfo() {
		$fields = array(
			'export_value',
			'export_description'
		);
		$inserts = array();
		foreach ($fields as $field) {
			if (in_array($field, $this->_updates)) {
				//IGNORE UPDATE IF VALUE LEFT EMPTY
				if(empty($this->{toCamelCaps($field)})) {
					continue;
				}
				$inserts[] = $field . ' = ' . $this->_trans->escape($this->{toCamelCaps($field)});
			}
		}

		if ($inserts) {
			$this->_trans->add('INSERT IGNORE INTO catalogue_export (catalogue_id, locale_id) VALUES '
					. '(' . $this->catalogueID . ',' . $this->_trans->escape($this->_locale->getId()) . ')');
			$this->_trans->add('UPDATE catalogue_export SET ' . implode(',', $inserts) . ' '
					. 'WHERE catalogue_id = ' . $this->catalogueID . ' '
					. 'AND locale_id = ' . $this->_trans->escape($this->_locale->getId()));
		}
	}


	protected function _updateProductImages() {
		foreach ($this->_images as $img) {
			if (isset($img->image_location)) {
				$this->_trans->add('REPLACE INTO catalogue_image (image_id, catalogue_id, locale_id, image_type_id, image_location, colour_id)'
								 . 'VALUES (' . $this->_db->null($img->image_id) . ',' . $img->catalogue_id . ','
								 . $this->_db->escape($this->_locale->getId()) . ','
								 . $img->image_type_id . ',' . $this->_db->escape($img->image_location) . ', '. (int)$img->colour_id .');');
			}
		}
	}


	protected function _setAttribute($attribute, $ids, $clear = true) {
		//REMOVE ATTRIBUTES FOR THIS VERSION AND RELOAD
		if($clear) {
			$query = 'DELETE FROM catalogue_' . $this->_corrected($attribute) . ' '
				   . 'WHERE catalogue_id = ' . $this->catalogueID;
			$this->_db->query($query);
			$this->_loadAttribute($attribute, true);
		}
		//COLLECT DIFFERENCES
		$changes = array(
			'add'    => array(),
			'remove' => array()
		);
		foreach ($ids as $id) {
			if (!isset($this->{'_' . toCamelCaps($attribute) . 's'}[$id])) {
				$changes['add'][] = $id;
			}
		}
		foreach ($this->{'_' . toCamelCaps($attribute) . 's'} as $id => $obj) {
			if (!in_array($id, $ids)) {
				$changes['remove'][] = $id;
			}
		}
		//SAVE CHANGES
		$inserts = array();
		foreach ($changes['add'] as $i => $id) {
			$inserts[] = '(' . $this->catalogueID . ',' . $id . ',1, '. $i . ')';
		}
		foreach ($changes['remove'] as $id) {
			$inserts[] = '(' . $this->catalogueID . ',' . $id . ',0, 0)';
		}

		if ($inserts) {
			$query = 'REPLACE INTO catalogue_' . $this->_corrected($attribute) . ' (catalogue_id, '
				   . $this->_corrected($attribute) . '_id, stack_addition, order_id) '
				   . 'VALUES ' . implode(',', $inserts);
			$this->_db->query($query);
		}
		$this->_loadAttribute($attribute, true);
	}


	protected function _updateUnits() {
		//LOOK FOR NEW UNITS
		foreach ($this->_units as $unit) {
			if (!$unit->unit_id) {
				$this->_createUnit($unit);
			}
		}

		//CREATE INSERTS
		$inserts = array(
			'weight'    => array(),
			'stock'     => array(),
			'price'     => array(),
			'cost' 		=> array(),
			'rrp' 		=> array(),
		);
		//FOR EACH PROPERTY, CREATE AN INSERT IF IT HAS A UNIQUE VALUE OR DELETE ANY SAVED VALUE IF IT DOES NOT
		foreach ($this->_units as $unit) {

			if ($unit->weight && ($unit->weight != $this->weight)) {
				$inserts['weight'][] = '(' . $unit->unit_id . ',' . (float) $unit->weight . ')';
			} else {
				$this->_trans->add('DELETE FROM catalogue_unit_weight WHERE unit_id = ' . $unit->unit_id);
			}
			//UPDATE UNIT SPECIFIC RETAIL PRICES
			if ($unit->getPrice($this->_locale->getCurrencyID()) && ($unit->getPrice($this->_locale->getCurrencyID()) != $this->price)) {
				$inserts['price'][] = '(' . $unit->unit_id . ',' . $this->_db->escape($this->_locale->getId())
									. ',' . (float) $unit->getPrice($this->_locale->getCurrencyID()) . ')';
			} else {
				$this->_trans->add('DELETE FROM catalogue_unit_price WHERE unit_id = ' . $unit->unit_id . ' AND locale_id = ' . $this->_trans->escape($this->_locale->getId()));
			}
			//UPDATE UNIT SPECIFIC WHOLESALE PRICES
			if ($unit->getPrice($this->_locale->getCurrencyID(), 'wholesale') && ($unit->getPrice($this->_locale->getCurrencyID(), 'wholesale') != $this->wholesalePrice)) {
				$inserts['wholesale'][] = '(' . $unit->unit_id . ',' . $this->_db->escape($this->_locale->getId())
									. ',' . (float) $unit->getPrice($this->_locale->getCurrencyID(), 'wholesale') . ')';
			} else {
				$this->_trans->add('DELETE FROM catalogue_unit_wholesale_price WHERE unit_id = ' . $unit->unit_id . ' AND locale_id = ' . $this->_trans->escape($this->_locale->getId()));
			}
			//UPDATE UNIT SPECIFIC COST PRICES
			if ($unit->getPrice($this->_locale->getCurrencyID(), 'cost') && ($unit->getPrice($this->_locale->getCurrencyID(), 'cost') != $this->costPrice)) {
				$inserts['cost'][] = '(' . $unit->unit_id . ',' . $this->_db->escape($this->_locale->getId())
									. ',' . (float) $unit->getPrice($this->_locale->getCurrencyID(), 'cost') . ')';
			} else {
				$this->_trans->add('DELETE FROM catalogue_unit_cost_price WHERE unit_id = ' . $unit->unit_id . ' AND locale_id = ' . $this->_trans->escape($this->_locale->getId()));
			}

			//UPDATE UNIT SPECIFIC RRP PRICES
			if ($unit->getPrice($this->_locale->getCurrencyID(), 'rrp') && ($unit->getPrice($this->_locale->getCurrencyID(), 'rrp') != $this->rrp)) {
				$inserts['rrp'][] = '(' . $unit->unit_id . ',' . $this->_db->escape($this->_locale->getId())
									. ',' . (float) $unit->getPrice($this->_locale->getCurrencyID(), 'rrp') . ')';
			} else {
				$this->_trans->add('DELETE FROM catalogue_unit_rrp WHERE unit_id = ' . $unit->unit_id . ' AND locale_id = ' . $this->_trans->escape($this->_locale->getId()));
			}

			$this->_trans->add('UPDATE catalogue_unit SET visible = '. (int)$unit->visible .' WHERE unit_id = ' . $unit->unit_id);
		}
		//ADD INSERTS TO TRANSACTION
		if ($inserts['weight']) {
			$this->_trans->add('REPLACE INTO catalogue_unit_weight (unit_id, weight) VALUES ' . implode(',', $inserts['weight']));
		}
		if ($inserts['stock']) {
			$this->_trans->add('REPLACE INTO catalogue_unit_stock (unit_id, stock) VALUES ' . implode(',', $inserts['stock']));
		}
		if ($inserts['price']) {
			$this->_trans->add('REPLACE INTO catalogue_unit_price (unit_id, locale_id, price) VALUES ' . implode(',', $inserts['price']));
		}
		if ($inserts['cost']) {
			$this->_trans->add('REPLACE INTO catalogue_unit_cost_price (unit_id, locale_id, cost_price) VALUES ' . implode(',', $inserts['cost']));
		}
		if ($inserts['rrp']) {
			$this->_trans->add('REPLACE INTO catalogue_unit_rrp (unit_id, locale_id, rrp) VALUES ' . implode(',', $inserts['rrp']));
		}
	}

	protected function _updateStock() {

		//CREATE INSERTS
		$inserts = array(
			'stock'    => array(),
		);
		//FOR EACH PROPERTY, CREATE AN INSERT IF IT HAS A UNIQUE VALUE OR DELETE ANY SAVED VALUE IF IT DOES NOT
		foreach ($this->_units as $unit) {
			foreach(getLocations() as $location_id => $location) {
				$inserts['stock'][] = '(' . $unit->unit_id . ',' . $location_id . ',' . (int) $unit->getStock($location_id) . ')';
			}
		}

		//ADD INSERTS TO TRANSACTION
		if ($inserts['stock']) {
			$this->_trans->add('REPLACE INTO catalogue_unit_stock (unit_id, location_id, stock) VALUES ' . implode(',', $inserts['stock']));
		}
	}


	protected function _createUnit(ProductUnit $unit) {

		$trans = new DBtransaction;
		$trans->add('INSERT INTO catalogue_unit (catalogue_id, unit_name) '
			      . 'VALUES (' . $this->catalogueID . ',' . $trans->escape($unit->sku) . ');');
		$trans->add('SET @UNIT_ID = LAST_INSERT_ID();');
		if ($unit->size_id) {
			$trans->add('INSERT INTO catalogue_unit_size (unit_id, size_id) '
			          . 'VALUES (@UNIT_ID,' . (int) $unit->size_id . ');');
		}
		if ($unit->colour_id) {
			$trans->add('INSERT INTO catalogue_unit_colour (unit_id, colour_id) '
			          . 'VALUES (@UNIT_ID,' . (int) $unit->colour_id . ');');
		}
		if ($unit->variant_id) {
			$trans->add('INSERT INTO catalogue_unit_variant (unit_id, variant_id) '
			          . 'VALUES (@UNIT_ID,' . (int) $unit->variant_id . ');');
		}
		if ($unit->barcode) {
			$trans->add('INSERT INTO catalogue_unit_barcode (unit_id, barcode) '
			          . 'VALUES (@UNIT_ID,' . $trans->escape($unit->barcode) . ');');
		}

		$trans->add('SELECT @UNIT_ID;');
		$trans->run();
		$unit->unit_id = $trans->value();

		if (!$unit->unit_id) {
			$this->fb->addError('Unable to create new unit: ' . $trans->error());
			return false;
		} else {
			$DB = new DBquery;
			$DB->query('INSERT INTO catalogue_unit_barcode (unit_id, barcode) VALUES ('.$unit->unit_id.', '.$this->_db->escape(Barcode::instance()->makeBarcode($unit->unit_id)).')');
		}
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



/********************************************************************
*
*   LOAD PRODUCT
*
********************************************************************/


	//FIRE ON CONSTRUCT
	protected function _loadProduct($productID, $versionID) {
		//LOAD THE PRODUCT
		$this->_loadProductCore($productID, $versionID);
		if ($this->productID) {
			//LOAD INFO FOR THE DEFAULT LOCALE
			$this->_loadProductInfo(Locale::DEFAULT_LOCALE_ID);
			$this->_loadExportInfo(Locale::DEFAULT_LOCALE_ID);
			//OVERLAY THE SELECTED LOCALE
			$this->_loadProductInfo($this->_locale->getId());
			$this->_loadExportInfo($this->_locale->getId());
		}
	}


	//LOAD CORE PRODUCT INFO
	protected function _loadProductCore($productID, $versionID) {
		if (!is_null($productID)) {
			$conditions = array(
				'catalogue.product_id = ' . $productID,
				'catalogue.date_deleted IS NULL'
			);
			if ($versionID) {
				$conditions[] = 'catalogue.version_id = ' . $versionID;
			}
			$query = 'SELECT catalogue_id, version_id, category_id, category_slug, category.section, product_id, product_year, product_name, tax_code, weight, default_cross_sell, bodypart_id, sizegroup_id, supplier_ref, product_key, brand.brand_id, brand_name, brand_slug '
				   . 'FROM catalogue '
				   . 'JOIN catalogue_product USING (product_id) '
				   . 'JOIN category USING (category_id) '
				   . 'JOIN brand USING (brand_id) '
				   . 'LEFT JOIN brand_info ON (brand.brand_id = brand_info.brand_id AND locale_id = '.$this->_db->escape($this->_locale->getId()).') '
				   . 'WHERE ' . implode(' AND ', $conditions) . ' '
				   . 'ORDER BY version_id DESC LIMIT 1';

			$this->_db->query($query);

			if ($product = $this->_db->row()) {
				foreach ($product as $key => $val) {
					$this->{toCamelCaps($key)} = $val;
				}
			}

		}
	}


	//LOAD PRODUCT INFO FOR A GIVEN LOCALE. UTLISE STACK WITH PRODUCT VERSIONING
	protected function _loadProductInfo($localeID) {
		//AVOID RUNNING TWICE FOR THE SAME LOCALE
		if ($localeID != $this->_static['savedLocaleID_1']) {
			$this->price = NULL;
			$this->rrp = NULL;
			$this->_static['savedLocaleID_1'] = $localeID;
			$skip = array(
				'locale_id',
				'catalogue_id'
			);
			$query = 'SELECT catalogue_info.* '
				   . 'FROM catalogue '
				   . 'JOIN catalogue_info USING (catalogue_id) '
				   . 'WHERE product_id = ' . $this->productID . ' '
				   . 'AND version_id <= ' . $this->versionID . ' '
				   . 'AND locale_id = ' . $this->_db->escape($localeID) . ' '
				   . 'ORDER BY version_id ASC ';
			$this->_db->query($query);
			while ($row = $this->_db->row()) {
				foreach ($row as $key => $val) {
					if (!is_null($val) && !in_array($key, $skip)) {
						$this->{toCamelCaps($key)} = $val;
					}
				}
			}
			if ($localeID == Locale::DEFAULT_LOCALE_ID) {
				$this->defaultDisplayName = $this->displayName;
			}
		}

	}


	//LOAD EXPORT INFORMATION
	protected function _loadExportInfo($localeID) {
		//AVOID RUNNING TWICE FOR THE SAME LOCALE
		if ($localeID != $this->_static['savedLocaleID_4']) {
			$this->_static['savedLocaleID_4'] = $localeID;
			$skip = array(
				'locale_id',
				'catalogue_id'
			);
			$query = 'SELECT catalogue_export.* '
				   . 'FROM catalogue '
				   . 'JOIN catalogue_export USING (catalogue_id) '
				   . 'WHERE product_id = ' . $this->productID . ' '
				   . 'AND version_id <= ' . $this->versionID . ' '
				   . 'AND locale_id = ' . $this->_db->escape($localeID) . ' '
				   . 'ORDER BY version_id ASC ';
			$this->_db->query($query);
			while ($row = $this->_db->row()) {
				foreach ($row as $key => $val) {
					if (!is_null($val) && !in_array($key, $skip)) {

						$this->{toCamelCaps($key)} = $val;

						if($localeID == $this->_locale->getId()) {
							$this->{'unstacked'.ucfirst(toCamelCaps($key))} = $val;
						}

					}
				}
			}
		}
	}


	protected function _loadAttribute($attribute, $force = false) {
		$func = '_load' . ucfirst(toCamelCaps($attribute)) . 's';
		if (method_exists($this, $func)) {
			$this->{$func}($force);
		} else if ($force || empty($this->{'_' . $attribute . 's'})) {
			$this->{'_' . $attribute . 's'} = array();
			$this->_static['savedLocaleID_2'][$attribute] = NULL;
			$this->_stackAttribute($attribute, Locale::DEFAULT_LOCALE_ID);
			$this->_stackAttribute($attribute, $this->_locale->getId());
		}
	}


	protected function _loadImages() {
		if (empty($this->_images)) {
			$this->_loadDefaultImages();
			$this->_stackImages(Locale::DEFAULT_LOCALE_ID);
			//$this->_stackImages($this->_locale->getId());
			ksort($this->_images);
		}
	}


	protected function _loadDefaultImages() {
		$query = 'SELECT image_type_id, image_type_name '
			   . 'FROM catalogue_image_type '
			   . 'ORDER BY image_type_id ASC';
		$this->_db->query($query);

		while ($row = $this->_db->row()) {
			$obj = new Image;
			foreach ($row as $key => $val) {
				$obj->{$key} = $val;
			}
			$this->_images[$obj->image_type_id][0] = $obj;
		}
	}


	protected function _stackAttribute($attribute, $localeID) {
		if (!isset($this->_static['savedLocaleID_2'][$attribute])) {
			$this->_static['savedLocaleID_2'][$attribute] = NULL;
		}
		//AVOID RUNNING TWICE FOR THE SAME LOCALE
		if ($localeID != $this->_static['savedLocaleID_2'][$attribute]) {
			$this->_static['savedLocaleID_2'][$attribute] = $localeID;
			$skip = array(
				'locale_id'
			);

			$class = ucfirst($this->_corrected($attribute));
			$query = 'SELECT val_' . $this->_corrected($attribute) . '.*, string_value, stack_addition '
				   . 'FROM catalogue '
				   . 'JOIN catalogue_' . $this->_corrected($attribute) . ' USING (catalogue_id) '
				   . 'JOIN val_' . $this->_corrected($attribute) . ' USING (' . $this->_corrected($attribute) . '_id) '
				   . 'JOIN locale_string USING (string_id) '
				   . 'WHERE product_id = ' . $this->productID . ' '
				   . 'AND version_id <= ' . $this->versionID . ' '
				   . 'AND locale_id = ' . $this->_db->escape($localeID) . ' '
				   . 'ORDER BY version_id ASC ';
			$this->_db->query($query);
			while ($row = $this->_db->row()) {
				$obj = new $class;
				foreach ($row as $key => $val) {
					$obj->{$key} = $val;
				}
				if (!$obj->stack_addition) {
					unset($this->{'_' . $attribute . 's'}[$obj->{$this->_corrected($attribute) . '_id'}]);
				} else {
					$this->{'_' . $attribute . 's'}[$obj->{$this->_corrected($attribute) . '_id'}] = $obj;
				}
			}
		}
	}


	//LOAD PRODUCT INFO FOR A GIVEN LOCALE. UTLISE STACK WITH PRODUCT VERSIONING
	protected function _stackImages($localeID) {
		//AVOID RUNNING TWICE FOR THE SAME LOCALE
		if ($localeID != $this->_static['savedLocaleID_3']) {
			$this->_static['savedLocaleID_3'] = $localeID;
			$skip = array(
				'locale_id'
			);

			$this->getColours();

			$query = 'SELECT catalogue_image.*, image_type_name '
				   . 'FROM catalogue '
				   . 'JOIN catalogue_image USING (catalogue_id) '
				   . 'JOIN catalogue_image_type USING (image_type_id) '
				   . 'WHERE product_id = ' . $this->productID . ' '
				   . 'AND version_id <= ' . $this->versionID . ' '
				   . 'AND locale_id = ' . $this->_db->escape($localeID) . ' '
				   . 'ORDER BY version_id ASC ';

			$this->_db->query($query);
			while ($row = $this->_db->row()) {
				$obj = new Image;
				foreach ($row as $key => $val) {
					$obj->{$key} = $val;
				}
				$obj->colour_name  = (isset($this->_colours[$obj->colour_id]) ? $this->_colours[$obj->colour_id]->string_value : '');
				$this->_images[$obj->image_type_id][$obj->colour_id] = $obj;
			}
		}
	}


	//LOAD UNITS
	protected function _loadUnits() {
		if (is_null($this->_units)) {
			$this->_units = array();
			//LOAD LISTS
			$this->getColours();
			$this->getSizes();
			$this->getVariants();
			$query = 'SELECT u.unit_id, u.unit_name AS sku, u.visible, size_id, colour_id, variant_id, SUM(order_qty) AS tmp_qty, (IF (stock IS NOT NULL, stock, 0) - IF(order_qty, SUM(order_qty), 0)) AS current_stock, w.weight, barcode, u.supplier_ref, IF(u.supplier_ref, u.supplier_ref, catalogue_product.supplier_ref) AS supplierRef, brand_name, brand_id '
				   . 'FROM catalogue_unit AS u '
				   . 'LEFT JOIN catalogue_unit_stock USING (unit_id) '
				   . 'LEFT JOIN catalogue_unit_stock_pending ON (u.unit_id = catalogue_unit_stock_pending.unit_id AND NOW() <  DATE_ADD(order_started,INTERVAL 10 MINUTE))'
				   . 'LEFT JOIN catalogue_unit_size AS s ON (u.unit_id = s.unit_id) '
				   . 'LEFT JOIN catalogue_unit_colour AS c ON (u.unit_id = c.unit_id) '
				   . 'LEFT JOIN catalogue_unit_variant AS v ON (u.unit_id = v.unit_id) '
				   . 'LEFT JOIN catalogue_unit_weight AS w ON (u.unit_id = w.unit_id) '
				   . 'LEFT JOIN catalogue_unit_barcode AS b ON (u.unit_id = b.unit_id) '
				   . 'LEFT JOIN catalogue USING (catalogue_id) '
				   . 'LEFT JOIN catalogue_product USING (product_id) '
				   . 'LEFT JOIN brand_info USING (brand_id) '
				   . 'WHERE u.catalogue_id = ' . $this->catalogueID . ' '
				   . 'GROUP BY unit_id';

			$this->_db->query($query);
			while ($row = $this->_db->row('OBJECT')) {
				$unit = new ProductUnit;
				foreach ($row as $key => $val) {
					$unit->{$key} = $val;
					$unit->{toCamelCaps($key)} = $val;
				}
				//GET LOCALISED STRINGS
				$unit->size    = (isset($this->_sizes[$unit->size_id]) ? $this->_sizes[$unit->size_id]->string_value : '');
				$unit->variant = (isset($this->_variants[$unit->variant_id]) ? $this->_variants[$unit->variant_id]->string_value : '');
				$unit->colour  = (isset($this->_colours[$unit->colour_id]) ? $this->_colours[$unit->colour_id]->string_value : '');

				//BUILD NAME
				$name = array();
				foreach (array('size', 'colour', 'variant') as $key) {
					if ($unit->{$key}) {
						$name[] = ucwords(strtolower($unit->{$key}));
					}
				}
				$unit->name = implode(', ', $name);
				//BUILD STYLE ID
				$styleID = $unit->colour_id . '.' . $unit->variant_id;
				$unit->style_id = ($styleID != '0.0' ? trim($styleID, '.') : NULL);
				//BUILD STYLE NAME
				$styleName = trim($unit->colour . ', ' . $unit->variant, ', ');
				$unit->style = ($styleName) ? $styleName : NULL;
				//BUILD DESCRIPTION
				$unit->description = $this->displayName;
				if ($unit->style) {
					$unit->description .= ', ' . $unit->style;
				}
				if ($unit->size) {
					$unit->description .= ', ' . $unit->size;
				}
				//ADD WEIGHT AND PRICE
				if (!$unit->weight) {
					$unit->weight = $this->weight;
				}
				//STACK SUPPLIER REF
				if (!$unit->supplierRef) {
					$unit->supplierRef = $this->supplierRef;
				}

				$this->_units[$unit->unit_id] = $unit;
				unset($unit);
			}
			$this->_loadUnitPrices();
			$this->_loadUnitStock();
		}
	}


	protected function _loadPrices() {
		if (is_null($this->_prices)) {
			$this->_prices = array();
			$query = 'SELECT price, wholesale_price, cost_price, sale_price, rrp, '
				   . "CONCAT(locale.locale_id, ':', currency_id) AS currency_id "
				   . 'FROM catalogue '
				   . 'JOIN catalogue_info USING (catalogue_id) '
				   . 'JOIN locale USING (locale_id) '
				   . 'WHERE product_id = ' . $this->productID . ' '
				   . 'AND version_id <= ' . $this->versionID . ' '
				   . 'AND price IS NOT NULL '
				   . 'ORDER BY version_id ASC, locale_id ASC';
			$this->_db->query($query);
			while ($row = $this->_db->row('OBJECT')) {
				$this->_prices['retail'][$row->currency_id] = $row->price;
				$this->_prices['wholesale'][$row->currency_id] = $row->wholesale_price;
				$this->_prices['sale'][$row->currency_id] = $row->sale_price;
				$this->_prices['cost'][$row->currency_id] = $row->cost_price;
				$this->_prices['rrp'][$row->currency_id] = $row->rrp;
			}
		}
		return $this->_prices;
	}


	protected function _loadUnitPrices() {
		//LOAD STACKED PRODUCT PRICING
		foreach ($this->_units as $unit) {
			foreach ($this->_loadPrices() as $type => $prices) {
				foreach ($prices as $currencyID => $price) {
					$unit->setPrice($price, $currencyID, $type);
				}
			}
		}
		//LOAD UNIT SPECIFIC PRICING
		$query = 'SELECT unit_id, price, wholesale_price, cost_price, rrp, '
			   . "CONCAT(locale.locale_id, ':', currency_id) AS currency_id "
			   . 'FROM catalogue_unit '
			   . 'JOIN locale ON (locale.locale_id = "'.$this->_locale->getId().'") '
			   . 'LEFT JOIN catalogue_unit_price USING (unit_id, locale_id)'
			   . 'LEFT JOIN catalogue_unit_wholesale_price USING (unit_id, locale_id) '
			   . 'LEFT JOIN catalogue_unit_cost_price USING (unit_id, locale_id) '
			   . 'LEFT JOIN catalogue_unit_rrp USING (unit_id, locale_id) '
			   . 'WHERE catalogue_id = ' . $this->catalogueID . ' '
			   . 'ORDER BY unit_id ASC, currency_id ASC';

		$this->_db->query($query);
		while ($row = $this->_db->row('OBJECT')) {
			if (isset($this->_units[$row->unit_id])) {
				if(!is_null($row->price)) {
					$this->_units[$row->unit_id]->setPrice($row->price, $row->currency_id);
				}
				if(!is_null($row->cost_price)) {
					$this->_units[$row->unit_id]->setPrice($row->cost_price, $row->currency_id, 'cost');
				}
				if(!is_null($row->rrp)) {
					$this->_units[$row->unit_id]->setPrice($row->rrp, $row->currency_id, 'rrp');
				}

				if (is_null($row->wholesale_price)) {
					$row->wholesale_price = $this->wholesalePrice;
				}
				$this->_units[$row->unit_id]->setPrice($row->wholesale_price, $row->currency_id, 'wholesale');
			}
		}
	}

	protected function _loadUnitStock() {
		$query  = 'SELECT unit_id, location_id, stock
				   FROM catalogue_unit
				   JOIN catalogue_unit_stock USING (unit_id)
				   WHERE catalogue_id = ' . $this->catalogueID;
		$this->_db->query($query);
		while($row = $this->_db->row('OBJECT')){
			if(isset($this->_units[$row->unit_id])) {
				$this->_units[$row->unit_id]->setStock($row->location_id, $row->stock);
			}
		}
	}


	protected function _sortUnits() {
		uasort($this->_units, array($this, '_compareStyle'));
		uasort($this->_units, array($this, '_compareSize'));
	}


	protected function _compareSize($unit1, $unit2) {
		return ($unit1->size_id > $unit2->size_id);
	}


	protected function _compareStyle($unit1, $unit2) {
		return (strcmp($unit1->style, $unit2->style));
	}


	//WORK AROUND FOR ODD PLURALS: SWATCH(E)S FOR EXAMPLE
	protected function _corrected($name) {
		switch ($name) {
			case 'swatche':
				$corrected = 'swatch';
				break;
			default:
				$corrected = $name;
		}
		return $corrected;
	}



/********************************************************************
*
*   HANDLE PRODUCT RANGES
*
********************************************************************/


	//NOTE THIS RETURNS range_name AS ARRAY KEY!
	protected function _getRangeList() {
		$ranges = array();
		$this->_db->query('SELECT range_id, range_name FROM val_range');
		if ($this->_db->error()) {
			throw new Exception('Error loading product ranges ' . $this->_db->error());
		}
		while ($row = $this->_db->row('OBJECT')) {
			$ranges[$row->range_name] = $row->range_id;
		}
		return $ranges;
	}


	protected function _saveRanges($names) {
		$ranges  = $this->_getRangeList();
		$inserts = array();
		foreach ($names as $name) {
			if (!isset($ranges[$name])) {
				$inserts[] = '(' . $this->_db->escape($name) . ')';
			}
		}
		if ($inserts) {
			$this->_db->query('INSERT IGNORE INTO val_range (range_name) VALUES ' . implode(',', $inserts));
			if ($this->_db->error()) {
				throw new Exception('Error saving product ranges');
			}
		}
	}



/********************************************************************
*
*   DELETE PRODUCT
*
********************************************************************/


	public function deleteVersion() {
		$this->_db->query('UPDATE catalogue SET date_deleted = NOW() WHERE catalogue_id = ' . $this->catalogueID);
		if ($this->_db->error()) {
			$this->_fb->addError('Unable to delete product version: databae error');
			return false;
		}
		$this->_fb->addSuccess('Successfully deleted ' . $this->productName . ' version ' . $this->versionID);
		return true;
	}




	public function deleteProduct() {
		$this->_db->query('UPDATE catalogue SET date_deleted = NOW() WHERE product_id = ' . $this->productID);
		if ($this->_db->error()) {
			$this->_fb->addError('Unable to delete product version: databae error');
			return false;
		}
		$this->_fb->addSuccess('Successfully deleted ' . $this->productName);
		return true;
	}

}
