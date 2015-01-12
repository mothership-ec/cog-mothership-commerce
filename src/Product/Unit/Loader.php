<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Mothership\Commerce\Product\Unit\LoaderInterface;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\ProductEntityLoaderInterface;
use Message\Mothership\Commerce\Product\Loader as ProductLoader;
use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;


class Loader implements ProductEntityLoaderInterface
{
	/**
	 * @var \Message\Cog\DB\Query
	 */
	protected $_query;

	/**
	 * @var \Message\Cog\Localisation\Locale
	 */
	protected $_locale;

	/**
	 * @var \Message\Mothership\Commerce\Product\Loader
	 */
	protected $_productLoader;

	protected $_loadInvisible  = true;
	protected $_loadOutOfStock = false;

	protected $_prices;
	protected $_defaultCurrency;

	protected $_returnArray = false;

	/**
	 * Load depencancies
	 *
	 * @param Query  $query  Query Object
	 * @param Locale $locale Locale Object
	 * @param array $prices
	 */
	public function __construct(Query $query, Locale $locale, array $prices, $defaultCurrency)
	{
		$this->_defaultCurrency = $defaultCurrency;
		$this->_query   = $query;
		$this->_locale  = $locale;
		$this->_prices  = $prices;
	}

	public function setProductLoader(ProductLoader $loader)
	{
		// @todo this should be set to product loader's include deleted flag
		$loader->includeDeleted(true);
		$this->_productLoader = $loader;
	}

	/**
	 * Load all the units for a given Product object
	 *
	 * @param  Product $product get all units of this object
	 *
	 * @return array|false      Array of units, or false if no units exist
	 */
	public function getByProduct(Product $product)
	{
		$result = $this->_query->run('
			SELECT
				unit_id
			FROM
				product_unit
			WHERE
				product_id = ?i
		', 	array(
				$product->id
			)
		);

		return count($result) ? $this->_load($result->flatten(), true, $product) : false;
	}

	public function getByID($unitID, $revisionID = null, Product $product = null)
	{
		return $this->_load($unitID, false, $product, $revisionID);
	}

	/**
	 * Get unit(s) by their barcode.
	 *
	 * Return value is always an array (an empty array of no `Unit`s found) if
	 * the `$barcode` parameter is passed as an array.
	 *
	 * @param  string|array $barcode Single barcode or array of barcodes
	 *
	 * @return array|Unit|false      Single unit or array of units, false or
	 *                               empty array if none found
	 */
	public function getByBarcode($barcode)
	{
		$alwaysReturnArray = is_array($barcode);

		if (!is_array($barcode)) {
			$barcode = array($barcode);
		}

		$result = $this->_query->run('
			SELECT
				unit_id
			FROM
				product_unit
			WHERE
				barcode IN (?js)
		', array($barcode));

		return count($result) ? $this->_load($result->flatten(), $alwaysReturnArray) : false;
	}

	public function includeInvisible($bool)
	{
		$this->_loadInvisible = $bool;

		return $this;
	}

	public function includeOutOfStock($bool)
	{
		$this->_loadOutOfStock = $bool;

		return $this;
	}

	/**
	 * @param int | array $unitIDs         $unitIDs Array or single untiID to load
	 * @param bool $alwaysReturnArray
	 * @param Product $product             $product Product associated to the product
	 * @param int | null $revisionID
	 * @throws \RuntimeException
	 *
	 * @return array|bool|mixed             Array of, or singular Unit object
	 */
	protected function _load($unitIDs, $alwaysReturnArray = false, Product $product = null, $revisionID = null)
	{
		// Load the data for the units
		$result = $this->_loadUnits($unitIDs, $revisionID);
		// Load stock levels
		$stock = $this->_loadStock($unitIDs);
		// Load the prices
		$prices = $this->_loadPrices($unitIDs);
		// Load the options
		$options = $this->_loadOptions($unitIDs, $revisionID);
		// Bind the results to the Unit Object
		$units = $result->bindTo(
			'Message\\Mothership\\Commerce\\Product\\Unit\\Unit',
			[
				$this->_locale,
				$this->_prices,
				$this->_defaultCurrency
			]
		);

		if (0 === count($result)) {
			return $alwaysReturnArray ? [] : false;
		}

		foreach ($result as $key => $data) {

			// Hide units which are not visible
			if (!$this->_loadInvisible && !$data->visible) {
				unset($units[$key]);
				continue;
			}

			// Save stock units
			foreach ($stock as $values) {
				if ($values->id == $data->id) {
					$units[$key]->stock[$values->location] = $values->stock;
				}
			}

			// Save unit options
			foreach ($options as $option) {
				if ($option->id == $data->id) {
					$units[$key]->options[$option->name] = $option->value;
				}
			}

			// Remove items that are out of stock if needed
			if (!$this->_loadOutOfStock && array_sum($units[$key]->stock) == 0) {
				unset($units[$key]);
				continue;
			}
			// Save prices to unit
			foreach ($prices as $price) {
				if ($price->id == $data->id) {
					$units[$key]->price[$price->type]->setPrice($price->currencyID, (float) $price->price, $this->_locale);
				}
			}

			// Set Authorship details
			$units[$key]->authorship->create(new DateTimeImmutable(date('c',$data->createdAt)), $data->createdBy);
			$units[$key]->visible = (bool)$data->visible;

			if ($data->updatedAt) {
				$units[$key]->authorship->update(new DateTimeImmutable(date('c',$data->updatedAt)), $data->updatedBy);
			}

			if ($data->deletedAt) {
				$units[$key]->authorship->delete(new DateTimeImmutable(date('c',$data->deletedAt)), $data->deletedBy);
			}

			if ($product) {
				$units[$key]->product = $product;
			}
			else {
				if (!$this->_productLoader) {
					throw new \RuntimeException('Cannot load product on unit(s) without a product loader instance');
				}

				$units[$key]->product = $this->_productLoader->getByID($data->product_id);
			}

			if (is_null($units[$key]->weight)) {
				$units[$key]->weight = $units[$key]->product->weight;
			}

		}

		// Reload the array to put the unitID as the key
		$ordered = array();
		foreach ($units as $unit) {
			$ordered[$unit->id] = $unit;
		}

		return $alwaysReturnArray  ? $ordered : reset($ordered);
	}

	/**
	 * Load the options for the given units
	 *
	 * @param  int|array $unitIDs UnitIDs to load options for
	 *
	 * @return Result 			  DB Result object
	 */
	protected function _loadOptions($unitIDs, $revisionID = null)
	{
		$getRevision = ' IFNULL(MAX(product_unit_info.revision_id),1) ';
		if ($revisionID) {
			$getRevision = $revisionID;
		}

		return $this->_query->run(
			'SELECT
				product_unit_option.unit_id      AS id,
				product_unit_option.option_name  AS name,
				product_unit_option.option_value AS value
			FROM
				product_unit_option
			LEFT JOIN
				product_unit_info ON (
					product_unit_info.unit_id = product_unit_option.unit_id
					AND product_unit_option.revision_id = (
						SELECT
							'.$getRevision.'
						FROM
							product_unit_info AS info
						WHERE
							info.unit_id = product_unit_option.unit_id
						GROUP BY unit_id
					)
				)
			WHERE
				product_unit_option.unit_id IN (:unitIDs?ij)
				'.(!is_null($revisionID) ? ' AND product_unit_option.revision_id = '.$getRevision.' ' : ''),
			array(
				'unitIDs' => (array) $unitIDs,
			)
		);
	}

	/**
	 * Load prices for the gievn units for each type. If there is no unit level
	 * specific pricing then it will use the Product price instead
	 *
	 * @param  int|array $unitIDs UnitIDs to load
	 *
	 * @return Result 			  DB Result object
	 */
	protected function _loadPrices($unitIDs)
	{
		return $this->_query->run(
			'SELECT
				product_unit.unit_id      AS id,
				product_price.type        AS type,
				product_price.currency_id AS currencyID,
				IFNULL(
					product_unit_price.price, product_price.price
				)     					  AS price
			FROM
				product_price
			JOIN
				product_unit ON (product_price.product_id = product_unit.product_id)
			LEFT JOIN
				product_unit_price 
			ON (
				product_unit.unit_id = product_unit_price.unit_id 
			AND 
				product_price.type = product_unit_price.type
			AND
				product_price.currency_id = product_unit_price.currency_id
			)
			WHERE
				product_unit.unit_id IN (?ij)
		', 	array(
				(array) $unitIDs,
			)
		);
	}

	/**
	 * Load the stock levels for each of the given units
	 *
	 * @param  int|array $unitIDs UnitIDs to load
	 *
	 * @return Result 			  DB Result object
	 */
	protected function _loadStock($unitIDs)
	{
		return $this->_query->run(
			'SELECT
				product_unit_stock.unit_id     	AS id,
				product_unit_stock.stock       	AS stock,
				product_unit_stock.location 	AS location
			FROM
				product_unit_stock
			WHERE
				product_unit_stock.unit_id IN (?ij)
		', 	array(
				(array) $unitIDs,
			)
		);
	}

	/**
	 * Load the attributes for the given unit IDs
	 *
	 * @param  int|array $unitIDs UnitIDs to load
	 *
	 * @return Result 			  DB Result object
	 */
	protected function _loadUnits($unitIDs, $revisionID = null)
	{

		$getRevision = '
			SELECT
				IFNULL(MAX(revision_id),1)
			FROM
				product_unit_info AS info
			WHERE
				info.unit_id = product_unit.unit_id
			GROUP BY
				unit_id';
		if ($revisionID) {
			$getRevision = $revisionID;
		}

		return $this->_query->run(
			'SELECT
				product_unit.product_id,
				product_unit.unit_id      	AS id,
				product_unit.weight_grams 	AS weight,
				product_unit_info.sku     	AS sku,
				product_unit.barcode      	AS barcode,
				product_unit.visible      	AS visible,
				product_unit.created_at   	AS createdAt,
				product_unit.created_by   	AS createdBy,
				product_unit.updated_at   	AS updatedAt,
				product_unit.updated_by   	AS updatedBy,
				product_unit.deleted_at   	AS deletedAt,
				product_unit.deleted_by   	AS deletedBy,
				product_unit.supplier_ref 	AS suppliderRef,
				IFNULL(product_unit_info.revision_id,1) AS revisionID
			FROM
				product_unit
			LEFT JOIN
				product_unit_info ON (
					product_unit_info.unit_id = product_unit.unit_id
					AND revision_id = ('.$getRevision.')
				)
			WHERE
				product_unit.unit_id IN (?ij)
			AND
				deleted_at IS NULL
			GROUP BY
				product_unit.unit_id',
			array(
				(array) $unitIDs,
			)
		);
	}
}