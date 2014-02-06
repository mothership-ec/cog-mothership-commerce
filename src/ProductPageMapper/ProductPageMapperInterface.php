<?php

namespace Message\Mothership\Commerce\ProductPageMapper;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;

interface ProductPageMapperInterface
{
	/**
	 * Get the left-most page for a unit.
	 *
	 * @param  Unit $unit
	 * @return Page
	 */
	public function getPageForProductUnit(Unit $unit);

	/**
	 * Get the left-most page for a product.
	 *
	 * @param  Product    $product
	 * @param  array|null $options Name => Value array, e.g. 'Colour' => 'Red'
	 * @return Page
	 */
	public function getPageForProduct(Product $product, array $options = null);

	/**
	 * Get all pages associated with a product.
	 *
	 * @param  Product     $product
	 * @param  array|null  $options Name => Value array, e.g. 'Colour' => 'Red'
	 * @return array[Page]
	 */
	public function getPagesForProduct(Product $product, array $options = null);
}