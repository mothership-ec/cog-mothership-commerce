<?php

namespace Message\Mothership\Ecommerce\ProductPageMapper;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;

interface PageProductMapperInterface
{
	/**
	 * Get the units associated with a page.
	 *
	 * @param  Page   $page
	 * @return array[Unit]
	 */
	public function getProductUnitsForPage(Page $page);

	/**
	 * Get the first product associated with a page.
	 *
	 * @param  Page   $page
	 * @return array[Product]
	 */
	public function getProductForPage(Page $page);

	/**
	 * Get the products associated with a page.
	 *
	 * @param  Page     $page
	 * @return array[Product]
	 */
	public function getProductsForPage(Page $page);
}