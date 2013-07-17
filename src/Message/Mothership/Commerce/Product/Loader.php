<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;

use Message\Cog\ValueObject\DateTimeImmutable;

class Loader
{
	protected $_query;
	protected $_entities;

	protected $_returnArray;

	public function __construct(Query $query, array $entities = array())
	{
		$this->_query = $query;
		$this->_entities = $entities;
	}

	public function getByID($productID)
	{
		$this->_returnArray = is_array($productID);

		return $this->_loadProduct($productID);
	}


	protected function _loadProduct($productIDs)
	{
		$result = $this->_query->run(
			'SELECT
				catalogue.product_id   AS id,
				catalogue.catalogue_id AS catalogueID,
				catalogue.year         AS year,
				catalogue.created_at   AS createdAt,
				catalogue.created_by   AS createdBy,
				catalogue.updated_at   AS updatedAt,
				catalogue.updated_by   AS updatedBy,
				catalogue.deleted_at   AS deletedAt,
				catalogue.deleted_by   AS deletedBy,
				catalogue.brand_id     AS brandID,
				catalogue.name         AS name,
				catalogue.tax_rate     AS taxRate,
				catalogue.supplier_ref AS supplierRef,
				catalogue.weight_grams AS weightGrams,

				catalogue_info.display_name      AS displayName,
				catalogue_info.season            AS season,
				catalogue_info.description       AS description,
				catalogue_info.fabric            AS fabric,
				catalogue_info.features          AS features,
				catalogue_info.care_instructions AS careInstructions,
				catalogue_info.short_description AS shortDescription,
				catalogue_info.sizing            AS sizing,
				catalogue_info.notes             AS notes,

				catalogue_export.export_description            AS exportDescription,
				catalogue_export.export_value                  AS exportValue,
				catalogue_export.export_manufacture_country_id AS exportManufactureCountryID
			FROM
				catalogue
			LEFT JOIN
				catalogue_info ON (catalogue.catalogue_id = catalogue_info.catalogue_id)
			LEFT JOIN
				catalogue_export ON (catalogue.catalogue_id = catalogue_export.catalogue_id)
			WHERE
				product_id 	 IN (?ij)
		', 	array(
				(array) $productIDs,
			)
		);

		return $this->_buildProduct($result);
	}

	protected function _buildProduct(Result $result)
	{
		$products = $result->bindTo('Message\\Mothership\\Commerce\\Product\\Product', array($this->_entities));

		foreach ($result as $key => $data) {

			$products[$key]->authorship->create(new DateTimeImmutable(date('c',$data->createdAt)), $data->createdBy);

			if ($data->updatedAt) {
				$products[$key]->authorship->update(new DateTimeImmutable(date('c',$data->updatedAt)), $data->updatedBy);
			}

			if ($data->deletedAt) {
				$products[$key]->authorship->delete(new DateTimeImmutable(date('c',$data->deletedAt)), $data->deletedBy);
			}
		}

		return count($products) == 1 && !$this->_returnArray ? array_shift($products) : $products;

	}

}
