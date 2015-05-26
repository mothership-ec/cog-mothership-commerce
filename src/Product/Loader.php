<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;
use Message\Cog\DB\Entity\EntityLoaderCollection;

use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\FileManager\File\Loader as FileLoader;
use Message\Mothership\Commerce\Product\Image\TypeCollection as ImageTypes;
use Message\Mothership\Commerce\Product\Tax\Strategy\TaxStrategyInterface;

class Loader
{
	/**
	 * @var Query
	 */
	protected $_query;

	/**
	 * @var Locale
	 */
	protected $_locale;

	/**
	 * @var bool
	 */
	protected $_includeDeleted = false;

	/**
	 * @var bool
	 */
	protected $_returnArray;

	/**
	 * @var Type\Collection
	 */
	protected $_productTypes;

	/**
	 * @var Type\DetailLoader
	 */
	protected $_detailLoader;

	/**
	 * @var EntityLoaderCollection
	 */
	protected $_entityLoaders;

	/**
	 * @var TaxStrategyInterface
	 */
	protected $_taxStrategy;

	/**
	 * @var array
	 */
	protected $_priceTypes;

	/**
	 * @var Collection
	 */
	private $_productCache;

	public function __construct(
		Query $query,
		Locale $locale,
		FileLoader $fileLoader,
		Type\Collection $productTypes,
		Type\DetailLoader $detailLoader,
		EntityLoaderCollection $entityLoaders,
		$priceTypes = array(),
		$defaultCurrency,
		TaxStrategyInterface $taxStrategy,
		Collection $productCache
	) {
		$this->_query           = $query;
		$this->_locale          = $locale;
		$this->_productTypes    = $productTypes;
		$this->_detailLoader    = $detailLoader;
		$this->_priceTypes      = $priceTypes;
		$this->_fileLoader      = $fileLoader;
		$this->_entityLoaders   = $entityLoaders;
		$this->_defaultCurrency = $defaultCurrency;
		$this->_taxStrategy     = $taxStrategy;
		$this->_productCache    = $productCache;
	}

	public function getEntityLoader($entityName)
	{
		$loader = $this->_entityLoaders->get($entityName);
		$loader->setProductLoader($this);

		return $loader;
	}

	public function getEntityLoaders()
	{
		return $this->_entityLoaders;
	}

	/**
	 * Sets $_includeDeleted
	 *
	 * @param  bool   $bool boolean $_includeDeleted is set to
	 * @return Loader returns $this for chainability
	 */
	public function includeDeleted($bool)
	{
		$this->_includeDeleted = (bool)$bool;
		return $this;
	}

	public function getByID($productID)
	{
		$this->_returnArray = is_array($productID);

		return $this->_loadProduct($productID);
	}

	public function getByUnitID($unitID)
	{
		$result = $this->_query->run(
			'SELECT
				product_id
			FROM
				product_unit
			WHERE
				unit_id = ?i',
			array(
				$unitID
			)
		);

		$this->_returnArray = false;

		return count($result) ? $this->_loadProduct($result->flatten()) : false;
	}

	public function getByCategory($name, $limit = null)
	{
		$this->_checkLimit($limit);

		$result = $this->_query->run('
			SELECT
				product_id
			FROM
				product
			WHERE
				category = ?s
		', $name);

		$this->_returnArray = true;

		return $this->_loadProduct($result->flatten(), $limit);
	}

	public function getByBrand($name, $limit = null)
	{
		$this->_checkLimit($limit);

		$result = $this->_query->run('
			SELECT
				product_id
			FROM
				product
			WHERE
				brand = ?s
		', $name);

		$this->_returnArray = true;

		return $this->_loadProduct($result->flatten(), $limit);
	}

	public function getByType($type)
	{
		$result = $this->_query->run("
			SELECT
				product_id
			FROM
				product
			WHERE
				`type` = ?s
		", $type);

		$this->_returnArray = true;

		return $this->_loadProduct($result->flatten());
	}

	public function getByDetail($detailName, $detailValue)
	{
		if ($detailValue instanceof \DateTime) {
			$detailValue = $detailValue->getTimestamp();
		}

		$result = $this->_query->run("
			SELECT
				product_id
			FROM
				product
			LEFT JOIN
				product_detail AS d
			USING
				(product_id)
			WHERE
				d.name = :detailName?s
			AND
				d.value = :detailValue?s
		", [
			'detailName'  => $detailName,
			'detailValue' => $detailValue,
		]);

		$this->_returnArray = true;

		return $this->_loadProduct($result->flatten());
	}

	public function getAll()
	{
		$result = $this->_query->run('
			SELECT
				product_id
			FROM
				product
			');

		$this->_returnArray = true;

		return count($result) ? $this->_loadProduct($result->flatten()) : [];
	}

	public function getByLimit($limit)
	{
		$this->_checkLimit($limit);

		$result = $this->_query->run('
			SELECT
				product_id
			FROM
				product
			LIMIT
				0, :limit?i
		', [
			'limit' => $limit,
		]);

		$this->_returnArray = true;

		return count($result) ? $this->_loadProduct($result->flatten()) : [];
	}

	public function getBySearchTerms($terms, $limit = null)
	{
		$this->_checkLimit($limit);

		$this->_returnArray = true;

		$terms = explode(' ', $terms);
		$minTermLength = 3;
		$searchFields = [
			'p.name',
			'p.category',
			'ui.sku',
		];

		$query = '(';
		$where = [];

		$searchParams = [];

		foreach ($terms as $i => $term) {
			if (strlen($term) >= $minTermLength) {
				$terms[$i] = $term = strtolower($term);

				$whereFields = [];
				foreach ($searchFields as $j => $field) {
					$whereFields[] = 'LOWER(' . $field . ') LIKE :term' . $i . '?s' . PHP_EOL;
					$where[] = implode(' OR ', $whereFields);
				}

				$searchParams['term' . $i] = '%' . $term . '%';
			}
		}


		$query .= implode(' OR ', $where ) . ')';

		$query = 'SELECT
				p.product_id
			FROM
				product AS p
			LEFT JOIN
				product_unit AS u
			USING
				(product_id)
			LEFT JOIN
				product_unit_info AS ui
			USING
				(unit_id)
			WHERE
				' . $query . '
			';

		$result = $this->_query->run($query, $searchParams);
		$result = array_unique($result->flatten());

		return $this->_loadProduct($result, $limit);

	}

	protected function _loadProduct($productIDs, $limit = null)
	{
		if (!is_array($productIDs)) {
			$productIDs = (array) $productIDs;
		}

		if (0 === $this->_entityLoaders->count()) {
			throw new \LogicException('Cannot load products when entity loaders are not set.');
		}

		$cachedProducts = [];

		// Load products from cache if limit is not set. If limit is set this complicates the following query so
		// this step is skipped.
		if (null === $limit) {
			foreach ($productIDs as $key => $id) {
				if ($this->_productCache->exists($id)) {
					$cachedProduct = $this->_productCache->get($id);
					if ($this->_includeDeleted || !$cachedProduct->authorship->isDeleted()) {
						$cachedProducts[] = $cachedProduct;
						unset($productIDs[$key]);
					}
				}
			}
		} else {
			$this->_checkLimit($limit);
		}

		// If there are no uncached products remaining, return the cached product/s
		if (count($productIDs) <= 0) {
			return $this->_returnArray ? $cachedProducts : array_shift($cachedProducts);
		}

		$result = $this->_query->run(
			'SELECT
				product.product_id   AS id,
				product.product_id   AS catalogueID,
				product.created_at   AS createdAt,
				product.created_by   AS createdBy,
				product.updated_at   AS updatedAt,
				product.updated_by   AS updatedBy,
				product.deleted_at   AS deletedAt,
				product.deleted_by   AS deletedBy,
				product.brand        AS brand,
				product.type		 AS type,
				product.name         AS name,
				product.category     AS category,
				product.tax_strategy AS taxStrategy,
				product.tax_rate     AS taxRate,
				product.supplier_ref AS supplierRef,
				product.weight_grams AS weight,

				product_info.display_name      AS displayName,
				product_info.sort_name         AS sortName,
				product_info.description       AS description,
				product_info.short_description AS shortDescription,
				product_info.notes             AS notes,

				product_export.export_description            AS exportDescription,
				product_export.export_value                  AS exportValue,
				product_export.export_manufacture_country_id AS exportManufactureCountryID,
				product_export.export_code                   AS exportCode
			FROM
				product
			LEFT JOIN
				product_info ON (product.product_id = product_info.product_id)
			LEFT JOIN
				product_export ON (product.product_id = product_export.product_id)
			WHERE
				product.product_id 	 IN (?ij)
				' . (!$this->_includeDeleted ? 'AND product.deleted_at IS NULL' : '' ) . '
			' . ($limit ? 'LIMIT 0, ' . (int) $limit : '') . '
		', 	array(
				(array) $productIDs,
			)
		);

		$tags = $this->_query->run(
			'SELECT
				product_tag.product_id  AS id,
				product_tag.name        AS name
			FROM
				product_tag
			WHERE
				product_tag.product_id IN (?ij)
		', array(
			(array) $productIDs,
		));

		$products = $result->bindTo(
			'Message\\Mothership\\Commerce\\Product\\ProductProxy',
			[$this->_locale, $this->_priceTypes, $this->_defaultCurrency, $this->_entityLoaders, clone $this->_taxStrategy] // clone strategy as if inclusive, different base tax rates.
		);

		foreach ($result as $key => $data) {

			// needs to set the product type if inclusive to get the base tax
			if ($products[$key]->getTaxStrategy()->getName() === 'inclusive') {
				$products[$key]->getTaxStrategy()->setProductType($data->type);
			}

			$data->taxRate     = (float) $data->taxRate;
			$data->exportValue = (float) $data->exportValue;

			$products[$key]->authorship->create(new DateTimeImmutable(date('c',$data->createdAt)), $data->createdBy);

			if ($data->updatedAt) {
				$products[$key]->authorship->update(new DateTimeImmutable(date('c',$data->updatedAt)), $data->updatedBy);
			}

			if ($data->deletedAt) {
				$products[$key]->authorship->delete(new DateTimeImmutable(date('c',$data->deletedAt)), $data->deletedBy);
			}

			foreach ($tags as $k => $tag) {
				if ($tag->id == $data->id) {
					$products[$key]->tags[$k] = $tag->name;
				}
			}

			$this->_loadType($products[$key], $data->type);

			// Add product to cache
			$this->_productCache->add($products[$key]);
		}

		// Merge cached products and main products array
		$products = array_merge($products, $cachedProducts);

		return count($products) == 1 && !$this->_returnArray ? array_shift($products) : $products;
	}

	protected function _loadType(Product $product, $type)
	{
		$product->type    = $this->_productTypes->get($type);
	}

	private function _isWholeNumber($value)
	{
		if (is_numeric($value)) {
			$int = (int) $value;

			return ($int == $value);
		}

		return false;
	}

	private function _checkLimit($limit)
	{
		if (null !== $limit && !$this->_isWholeNumber($limit)) {
			throw new \InvalidArgumentException('Limit must be a whole number');
		}
	}

}
