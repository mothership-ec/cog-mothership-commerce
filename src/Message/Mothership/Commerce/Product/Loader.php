<?php

namespace Message\Mothership\Commerce\Product;

class Loader
{

	public function getByID()
	{

	}


	protected function _buildProduct($productIDs)
	{
		$result = $this->_query->run(
			'SELECT
				catalogue.product_id             AS id,
				catalogue.catalogue_id           AS catalogueID,
				catalogue.`year`                 AS year,
				catalogue.created_at             AS createdAt,
				catalogue.created_by             AS createdBy,
				catalogue.updated_at             AS updatedAt,
				catalogue.updated_by             AS updatedBy,
				catalogue.deleted_at             AS deletedAt,
				catalogue.deleted_by             AS deletedBy,
				catalogue.brand_id               AS brandID,
				catalogue.`name`                 AS name,
				catalogue.tax_rate               AS taxRate,
				catalogue.supplier_ref           AS supplierRef,
				catalogue.weight_grams           AS weightGrams,

				catalogue_info.display_name      AS displayName,
				catalogue_info.season            AS season,
				catalogue_info.description       AS description,
				catalogue_info.fabric            AS fabric,
				catalogue_info.features          AS features,
				catalogue_info.care_instructions AS careInstructions,
				catalogue_info.short_description AS shortDescription,
				catalogue_info.sizing            AS sizing,
				catalogue_info.notes             AS notes
			FROM
				catalogue
			LEFT JOIN
				catalogue_info ON (catalogue.catalogue_id = catalogue_info.catalogue_id)
			WHERE
				product_id 	 IN (?ij)
		', array(
			(array) $productIDs,
			)
		);

		$this->_loadProduct($result);
	}

	protected function _loadProduct(Result $result)
	{
		$products = $results->bindTo('Message\\Mothership\\Commerce\\Product\\Product');
		de($products);
	}

}
