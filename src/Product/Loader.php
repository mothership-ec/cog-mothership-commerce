<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;
use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\FileManager\File\Loader as FileLoader;
use Message\Mothership\Commerce\Product\Image\TypeCollection as ImageTypes;

class Loader
{
	protected $_query;
	protected $_locale;
	protected $_entities;
	protected $_includeDeleted = false;

	protected $_returnArray;
	protected $_productTypes;
	protected $_detailLoader;
	protected $_imageLoader;

	public function __construct(
		Query $query,
		Locale $locale,
		FileLoader $fileLoader,
		Type\Collection $productTypes,
		Type\DetailLoader $detailLoader,
		array $entities = array(),
		$priceTypes = array(),
		Image\Loader $imageLoader
	) {
		$this->_query			= $query;
		$this->_locale			= $locale;
		$this->_entities		= $entities;
		$this->_productTypes	= $productTypes;
		$this->_detailLoader	= $detailLoader;
		$this->_priceTypes		= $priceTypes;
		$this->_fileLoader		= $fileLoader;
		$this->_imageLoader     = $imageLoader;
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

	public function getEntityLoader($name)
	{
		if (!array_key_exists($name, $this->_entities)) {
			throw new \InvalidArgumentException(sprintf('Unknown product entity: `%s`', $name));
		}

		$this->_entities[$name]->setProductLoader($this);

		return $this->_entities[$name];
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

	public function getByCategory($name)
	{
		$result = $this->_query->run('
			SELECT
				product_id
			FROM
				product
			WHERE
				category = ?s
		', $name);

		$this->_returnArray = true;

		return $this->_loadProduct($result->flatten());
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
		$result = $this->_query->run(
			'SELECT
				product_id
			FROM
				product'
		);

		$this->_returnArray = true;

		return count($result) ? $this->_loadProduct($result->flatten()) : array();
	}


	protected function _loadProduct($productIDs)
	{
		if (!is_array($productIDs)) {
			$productIDs = (array) $productIDs;
		}

		if (!$productIDs) {
			return $this->_returnArray ? array() : false;
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
				product_export.export_manufacture_country_id AS exportManufactureCountryID
			FROM
				product
			LEFT JOIN
				product_info ON (product.product_id = product_info.product_id)
			LEFT JOIN
				product_export ON (product.product_id = product_export.product_id)
			WHERE
				product.product_id 	 IN (?ij)
				' . (!$this->_includeDeleted ? 'AND product.deleted_at IS NULL' : '' ) . '
		', 	array(
				(array) $productIDs,
			)
		);

		$prices = $this->_query->run(
			'SELECT
				product_price.product_id  AS id,
				product_price.type        AS type,
				product_price.currency_id AS currencyID,
				product_price.price       AS price
			FROM
				product_price
			WHERE
				product_price.product_id IN (?ij)
		', array(
			(array) $productIDs,
		));

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

		$products = $result->bindTo('Message\\Mothership\\Commerce\\Product\\Product', array($this->_locale, $this->_entities, $this->_priceTypes));

		foreach ($result as $key => $data) {

			$data->taxRate     = (float) $data->taxRate;
			$data->exportValue = (float) $data->exportValue;

			$products[$key]->authorship->create(new DateTimeImmutable(date('c',$data->createdAt)), $data->createdBy);

			if ($data->updatedAt) {
				$products[$key]->authorship->update(new DateTimeImmutable(date('c',$data->updatedAt)), $data->updatedBy);
			}

			if ($data->deletedAt) {
				$products[$key]->authorship->delete(new DateTimeImmutable(date('c',$data->deletedAt)), $data->deletedBy);
			}

			foreach ($prices as $price) {
				if ($price->id == $data->id) {
					$products[$key]->price[$price->type]->setPrice($price->currencyID, (float) $price->price, $this->_locale);
				}
			}

			foreach ($tags as $k => $tag) {
				if ($tag->id == $data->id) {
					$products[$key]->tags[$k] = $tag->name;
				}
			}

			$images = $this->_imageLoader->getByProduct($products[$key]);
			$products[$key]->images = $images;

			$this->_loadType($products[$key], $data->type);
		}

		return count($products) == 1 && !$this->_returnArray ? array_shift($products) : $products;
	}

	protected function _loadType(Product $product, $type)
	{
		$product->details = $this->_detailLoader->load($product);
		$product->type    = $this->_productTypes->get($type);
	}

}
