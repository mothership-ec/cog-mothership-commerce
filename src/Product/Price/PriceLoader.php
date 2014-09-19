<?php 

namespace Message\Mothership\Commerce\Product\Price;

use Message\Cog\DB\Query;
use Message\Cog\Localisation\Locale;
use Message\Mothership\Commerce\Product\ProductEntityLoaderInterface;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Loader as ProductLoader;

class PriceLoader implements ProductEntityLoaderInterface
{
	protected $_query;
	protected $_locale;
	protected $_productLoader;

	public function __construct(Query $query, Locale $locale)
	{
		$this->_query  = $query;
		$this->_locale = $locale;
	}

	public function getByProduct(Product $product)
	{
		$result = $this->_query->run(
			'SELECT
				product_price.product_id  AS id,
				product_price.type        AS type,
				product_price.currency_id AS currencyID,
				product_price.price       AS price
			FROM
				product_price
			WHERE
				product_price.product_id = ?i
		', $product->id
		);

		$prices = [];
		foreach ($result as $priceRaw) {
			$price    = new TypedPrice($priceRaw->type, $this->_locale);
			$price->setPrice($priceRaw->currencyID, (float) $priceRaw->price, $this->_locale);

			$prices[] = $price;
		}
		
		return $prices;
	}

	public function setProductLoader(ProductLoader $productLoader)
	{
		$this->_productLoader = $productLoader;
	}
}