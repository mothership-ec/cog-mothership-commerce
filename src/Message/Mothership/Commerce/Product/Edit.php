<?php

namespace Message\Mothership\Commerce\Product;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\User\User;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;

class Edit
{
	protected $_query;
	protected $_user;

	public function __construct(Query $query, $user = null)
	{
		$this->_query = $query;
		$this->_user = $user;
	}

	public function save(Product $product)
	{

		$date = new DateTimeImmutable();

		$product->authorship->update($date, $this->_user->id);

		$result = $this->_query->run(
			'UPDATE
				product
			 LEFT JOIN
			 	product_info ON (product.product_id = product_info.product_id)
			 SET
				product.year = :year?i,
				product.updated_at = :updated_at?i,
				product.updated_by = :updated_by?i,
				product.brand_id = :brand_id?i,
				product.name = :name?i,
				product.tax_rate = :tax_rate?,
				product.supplier_ref = :supplier_ref?s,
				product.weight_grams = :weight_grams?i,

				product_info.display_name = :display_name?s,
				product_info.season = :season?s,
				product_info.description = :description?i,
				product_info.fabric = :fabric?i,
				product_info.features = :features?i,
				product_info.care_instructions = :care_instructions?i,
				product_info.short_description = :short_description?i,
				product_info.sizing = :sizing?s,
				product_info.notes = :notes?s
			WHERE
				product.product_id = :productID?i
			', array(
				'year'              => $product->year,
				'updated_at'        => $product->authorship->updatedAt(),
				'udpated_by'        => $product->authorship->updatedBy(),
				'brand_id'          => $product->brandID,
				'name'              => $product->name,
				'tax_rate'          => $product->taxRate,
				'supplier_ref'      => $product->supplierRef,
				'weight_grams'      => $product->weightGrams,
				'display_name'      => $product->displayName,
				'season'            => $product->season,
				'description'       => $product->description,
				'fabric'            => $product->fabric,
				'features'          => $product->features,
				'care_instructions' => $product->careInstructions,
				'short_description' => $product->shortDescription,
				'sizing'            => $product->sizing,
				'notes'             => $product->notes,
				'productID'			=> $product->id,
			)
		);

		return $product;
	}

}
