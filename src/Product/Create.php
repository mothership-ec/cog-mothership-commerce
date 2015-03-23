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

				product.type			= :type?s,
				product.name			= :name?s,
				product.weight_grams	= :weight?i,
				product.tax_rate		= :tax_rate?f,
				product.tax_strategy	= :tax_strat?s,
				product.supplier_ref    = :supplier?s,
				product.created_at		= :created_at?d,
				product.created_by		= :created_by?i,
				product.brand           = :brand?s,
				product.category        = :category?s
				',
			[
				'type'       => $product->type->getName(),
				'name'       => $product->name,
				'weight'     => $product->weight,
				'tax_rate'   => $product->taxRate,
				'tax_strat'  => $this->_defaultTaxStrategy->getName(),
				'supplier'   => $product->supplierRef,
				'created_at' => $product->authorship->createdAt(),
				'created_by' => $product->authorship->createdBy()->id,
				'brand'      => $product->getBrand(),
				'category'   => $product->getCategory(),
			]
		);

		$productID = $result->id();

		$info = $this->_query->run(
			'INSERT INTO
				product_info
			SET
				product_id        = :id?i,
				locale            = :locale?s,
				display_name      = :displayName?s,
				sort_name         = :sortName?s,
				description       = :description?s,
				short_description = :shortDesc?s',
			[
				'id'          => $productID,
				'locale'      => $this->_locale->getID(),
				'displayName' => $product->displayName,
				'sortName'    => $product->sortName,
				'description' => $product->description,
				'shortDesc'   => $product->shortDescription,
			]
		);

		$queryAppend = [];
		$queryVars   = [];
		foreach($this->_priceTypes as $type) {
			foreach($this->_currencyIDs as $currency) {
				$price = $product->getPrices()->exists($type) ? $product->getPrice($type, $currency) : 0;
				$queryAppend[] = "(?i, ?s, ?f, ?s, ?s)";
				$vars          = [
					$productID,
					$type,
					$price,
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
