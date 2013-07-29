<?php

namespace Message\Mothership\Commerce\Product;

use Message\Mothership\Commerce\Product\Product;

use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\Localisation\Locale;
use Message\Cog\DB\Query;
use Message\Cog\DB\Result;

use Message\User\UserInterface;

class Create
{
	protected $_query;
	protected $_locale;
	protected $_user;

	public function __construct(Query $query, Locale $locale, UserInterface $user)
	{
		$this->_query  = $query;
		$this->_locale = $locale;
		$this->_user   = $user;
	}

	public function save(Product $product)
	{
		$result = $this->_query->run(
			'INSERT INTO
				product
			SET
				product.product_id   = null,
				product.name         = ?s,
				product.weight_grams = ?i,
				product.tax_rate     = ?,
				product.supplier_ref = ?s,
				product.created_at   = ?d,
				product.created_by   = ?i',
			array(
				$product->name,
				$product->weight,
				$product->taxRate,
				$product->supplierRef,
				$product->authorship->createdAt(),
				$product->authorship->createdBy()->id
			)
		);

		$productID = $result->id();

		$info = $this->_query->run(
			'INSERT INTO
				product_info
			SET
				product_info.product_id = ?i,
				product_info.locale = ?s,
				product_info.display_name = ?s,
				product_info.short_description   = ?s',
			array(
				$productID,
				$this->_locale->getID(),
				$product->displayName,
				$product->shortDescription,
			)
		);

		$product->id = $productID;

		return $product;
	}
}
