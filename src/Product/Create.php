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
	protected $_priceTypes;
	protected $_currencyIDs;

	protected $_defaultTaxStrategy = 'inclusive';

	public function __construct(Query $query, 
		Locale $locale, 
		UserInterface $user, 
		array $priceTypes, 
		array $currencyIDs)
	{
		$this->_query        = $query;
		$this->_locale       = $locale;
		$this->_user         = $user;
		$this->_priceTypes   = $priceTypes;
		$this->_currencyIDs  = $currencyIDs;
	}

	public function setDefaultTaxStrategy($strategy)
	{
		$this->_defaultTaxStrategy = $strategy;
	}

	public function save(Product $product)
	{
		return $this->create($product);
	}

	public function create(Product $product)
	{
		$result = $this->_query->run(
			'INSERT INTO
				product
			SET
				product.type			= ?s,
				product.name			= ?s,
				product.weight_grams	= ?i,
				product.tax_rate		= ?f,
				product.tax_strategy	= ?s,
				product.supplier_ref    = ?s,
				product.created_at		= ?d,
				product.created_by		= ?i',
			array(
				$product->type->getName(),
				$product->name,
				$product->weight,
				$product->taxRate,
				$this->_defaultTaxStrategy->getName(),
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

		$queryAppend = [];
		$queryVars   = [];
		foreach($this->_priceTypes as $type) {
			foreach($this->_currencyIDs as $currency) {
				$queryAppend[] = "(?i, ?s, 0, ?s, ?s)";
				$vars          = [
					$productID,
					$type,
					$currency,
					$this->_locale->getId(),
				];

				$queryVars = array_merge($queryVars, $vars);
			}
		}

		$defaultPrices = $this->_query->run(
			'INSERT INTO 
				product_price (product_id, type, price, currency_id, locale)
			VALUES
				' . implode(', ', $queryAppend),
			$queryVars
			);
	
		$product->id = $productID;

		return $product;
	}
}
