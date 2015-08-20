<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\ProductEntityLoaderInterface;
use Message\Mothership\Commerce\Product\Loader as ProductLoader;
use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\Cog\DB\Entity\EntityLoaderCollection;
use Message\Cog\DB;
use Message\Cog\DB\Result;


class Loader implements ProductEntityLoaderInterface
{
	/**
	 * @var \Message\Cog\DB\QueryBuilderFactory
	 */
	protected $_queryBuilderFactory;

	/**
	 * @var \Message\Cog\DB\QueryBuilder
	 */
	protected $_queryBuilder;

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

	private $_entityLoaderCollection;

	public function __construct(DB\QueryBuilderFactory $queryBuilderFactory, Locale $locale, array $prices, $defaultCurrency)
	{
		$this->_defaultCurrency = $defaultCurrency;
		$this->_queryBuilderFactory   = $queryBuilderFactory;
		$this->_locale  = $locale;
		$this->_prices  = $prices;
		$this->_entityLoaderCollection = new EntityLoaderCollection();
	}

	public function setProductLoader(ProductLoader $loader)
	{
		// @todo this should be set to product loader's include deleted flag
		$loader->includeDeleted(true);
		$this->_productLoader = $loader;
		$this->_entityLoaderCollection->add('product', $this->_productLoader);
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
		$this->_buildQuery();

		$this->_returnArray = true;

		$this->_queryBuilder->where('product_unit.product_id = ?i', [$product->id]);

		return $this->_loadFromQuery($product);
	}

	public function getByID($unitID, $revisionID = null, Product $product = null)
	{
		$this->_buildQuery($revisionID);

		$this->_returnArray = is_array($unitID);

		if (!is_array($unitID)) {
			$unitID = [$unitID];
		}

		$this->_queryBuilder->where('product_unit.unit_id IN (?ji)', $unitID);

		return $this->_loadFromQuery($product);
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
		$this->_returnArray = is_array($barcode);

		if (!is_array($barcode)) {
			$barcode = [$barcode];
		}

		$this->_buildQuery();

		$this->_queryBuilder->where('product_unit.barcode IN (?js)', $barcode);

		return $this->_loadFromQuery();
	}

	/**
	 * Gets the sale units
	 * 
	 * @return array The units which are in sale
	 */
	public function getSaleUnits($currency = null)
	{
		if ($currency === null) {
			$currency = $this->_defaultCurrency;
		}

		$saleQuery = $this->_queryBuilderFactory
			->getQueryBuilder()
			->select('rrp.unit_id AS unit_id')
			->from('rrp', $this->_getPriceQuery('rrp'))
			->join('retail', '
				retail.unit_id = rrp.unit_id AND
				rrp.currency_id = retail.currency_id AND
				(rrp.price - retail.price) > 0 AND
				rrp.currency_id = :currency?s
			', $this->_getPriceQuery('retail'))
			->addParams(['currency' => $currency])
		;

		$this->_returnArray = true;
		$this->_buildQuery();
		$this->_queryBuilder->where('product_unit.unit_id IN (?q)', $saleQuery);

		return $this->_loadFromQuery();
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

	private function _buildQuery($revisionID = null)
	{
		$getRevision = $revisionID ?
			$this->_queryBuilderFactory->getQueryBuilder()
				->select(':revisionID?i AS revisionID')
				->from('product_unit_info')
				->addParams(['revisionID' => $revisionID])
				->groupBy('revisionID')
			:
			$this->_queryBuilderFactory->getQueryBuilder()
				->select('IFNULL(MAX(revision_id), 1)')
				->from('info', 'product_unit_info')
				->where('info.unit_id = product_unit.unit_id')
				->groupBy('unit_id')
		;

		$this->_queryBuilder = $this->_queryBuilderFactory->getQueryBuilder()
			->select([
				// Unit info
				'product_unit.product_id    AS productID',
				'product_unit.unit_id      	AS id',
				'product_unit.weight_grams 	AS weight',
				'product_unit_info.sku     	AS sku',
				'product_unit.barcode      	AS barcode',
				'product_unit.visible      	AS visible',
				'product_unit.created_at   	AS createdAt',
				'product_unit.created_by   	AS createdBy',
				'product_unit.updated_at   	AS updatedAt',
				'product_unit.updated_by   	AS updatedBy',
				'product_unit.deleted_at   	AS deletedAt',
				'product_unit.deleted_by   	AS deletedBy',
				'product_unit.supplier_ref 	AS supplierRef',
				'IFNULL(product_unit_info.revision_id,1) AS revisionID',
				'product_unit_info.sku      AS sku',

				// Stock
				'product_unit_stock.stock    AS stock',
				'product_unit_stock.location AS stockLocation',

				// Prices
				'product_price.type AS priceType',
				'product_price.currency_id AS currencyID',
				'IFNULL(product_unit_price.price, product_price.price) AS price',

				// Options
				'product_unit_option.option_name  AS optionName',
				'product_unit_option.option_value AS optionValue',
			])
			->from('product_unit')
			->leftJoin('product_unit_info', '
				product_unit_info.unit_id = product_unit.unit_id AND
				revision_id = (:revisionID?q)
			')
			->leftJoin('product_unit_stock', 'product_unit.unit_id = product_unit_stock.unit_id')
			->leftJoin('product_price', 'product_unit.product_id = product_price.product_id')
			->leftJoin('product_unit_price', '
				product_unit.unit_id = product_unit_price.unit_id AND
				product_price.type = product_unit_price.type AND
				product_price.currency_id = product_unit_price.currency_id
			')
			->leftJoin('product_unit_option', 'product_unit_option.unit_id = product_unit.unit_id')
			->addParams(['revisionID' => $getRevision])
		;

		if (!$this->_loadInvisible) {
			$this->_queryBuilder->where('product_unit.visible = ?i', [0]);
		}
	}

	private function _loadFromQuery(Product $product = null)
	{
		if (null === $this->_queryBuilder) {
			throw new \LogicException('Cannot load from query as query has not been built yet');
		}

		$result = $this->_queryBuilder->run();

		$units = [];

		foreach ($result as $row) {
			if (!array_key_exists($row->id, $units)) {
				$unit = new UnitProxy(
					$this->_entityLoaderCollection,
					$this->_locale,
					$this->_prices,
					$this->_defaultCurrency
				);
				$unit->id          = $row->id;
				$unit->barcode     = $row->barcode;
				$unit->weight      = $row->weight;
				$unit->supplierRef = $row->supplierRef;

				$unit->setSKU($row->sku);
				$unit->setVisible((bool) $row->visible);

				if ($product) {
					$unit->setProduct($product);
				} else {
					$unit->setProductID($row->productID);
				}

				$unit->authorship->create(new DateTimeImmutable(date('c', $row->createdAt)), $row->createdBy);

				if ($row->updatedAt) {
					$unit->authorship->update(new DateTimeImmutable(date('c', $row->updatedAt)), $row->updatedBy);
				}

				if ($row->deletedAt) {
					$unit->authorship->delete(new DateTimeImmutable(date('c', $row->deletedAt)), $row->deletedBy);
				}

				$units[$row->id] = $unit;
			}

			$unit = $units[$row->id];

			if (!array_key_exists($row->optionName, $unit->options)) {
				$unit->options[$row->optionName] = $row->optionValue;
			}

			if (!array_key_exists($row->stockLocation, $unit->stock)) {
				$unit->stock[$row->stockLocation] = $row->stock;
			}

			$unit->setPrice($row->price, $row->priceType, $row->currencyID);
		}

		return $this->_returnArray ? $units : array_shift($units);
	}

	private function _getPriceQuery($type)
	{
		if (!is_string($type)) {
			throw new \InvalidArgumentException('Price type must be a string, ' . gettype($type) . ' given');
		}

		return $this->_queryBuilderFactory
			->getQueryBuilder()
			->select([
				'product_unit.unit_id AS unit_id',
				'product_price.type AS `type`',
				'product_price.currency_id AS currency_id',
				'IFNULL (product_unit_price.price, product_price.price) AS price'
			])
			->from('product_price')
			->join('product_unit', 'product_price.product_id = product_unit.product_id)')
			->leftJoin('product_unit_price', '
				product_unit.unit_id = product_unit_price.unit_id AND
				product_price.type = product_unit_price.type AND
				product_price.currency_id = product_unit_price.currency_id
			')
			->where('product_price.type = ?s', [$type])
			;
	}
}