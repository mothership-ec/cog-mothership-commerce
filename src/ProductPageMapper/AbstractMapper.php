<?php

namespace Message\Mothership\Ecommerce\ProductPageMapper;

use Message\Cog\DB;
use Message\Mothership\CMS\Page;
use Message\Mothership\Commerce\Product;
use Message\Mothership\Commerce\Product\Unit;

/**
 * Abstract product page mapper for defining the relationship between products
 * and pages.
 *
 * Child classes should implement the `getPagesForProduct` and
 * `getProductsForPage` methods.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
abstract class AbstractMapper implements ProductPageMapperInterface, PageProductMapperInterface
{
	protected $_query;
	protected $_pageLoader;
	protected $_pageAuth;
	protected $_productLoader;
	protected $_unitLoader;

	protected $_validFieldNames = [];
	protected $_validGroupNames = [];
	protected $_validPageTypes  = [];

	protected $_filters = [];

	/**
	 * Constructor.
	 *
	 * @param DB\Query           $query
	 * @param Page\Loader        $pageLoader
	 * @param Page\Authorisation $pageAuth
	 * @param Product\Loader     $productLoader
	 * @param Unit\Loader        $unitLoader
	 */
	public function __construct(
		DB\Query           $query,
		Page\Loader        $pageLoader,
		Page\Authorisation $pageAuth,
		Product\Loader     $productLoader,
		Unit\Loader        $unitLoader
	) {
		$this->_query         = $query;
		$this->_pageLoader    = $pageLoader;
		$this->_pageAuth      = $pageAuth;
		$this->_productLoader = $productLoader;
		$this->_unitLoader    = $unitLoader;
	}

	/**
	 * @{inheritDoc}
	 */
	public function addFilter($callable)
	{
		if (!is_callable($callable)) {
			throw new \InvalidArgumentException('Filters for ProductPageFinder must be callable.');
		}

		$this->_filters[] = $callable;
	}

	/**
	 * Set the group name(s) against which the relationship will be matched.
	 *
	 * If set to `false` it will only match pages not in groups. If set to
	 * `null` or an empty array it will match pages in any group.
	 *
	 * @param  string|array|false $group
	 * @return void
	 */
	public function setValidGroupNames($group)
	{
		if (false !== $group) {
			if (!$group) $group = array();
			if (!is_array($group)) $group = array($group);
		}

		$this->_validGroupNames = $group;
	}

	/**
	 * Set the field name(s) against which the relationship will be matched.
	 *
	 * @param  string|array $field
	 * @return void
	 */
	public function setValidFieldNames($field)
	{
		if (!$field) $field = array();

		if (!is_array($field)) $field = array($field);

		$this->_validFieldNames = $field;
	}

	/**
	 * Set the page type(s) that are included in the mapping.
	 *
	 * @param  string|array|false $type
	 * @return void
	 */
	public function setValidPageTypes($type)
	{
		if (!$type) $type = array();

		if (!is_array($type)) $type = array($type);

		$this->_validPageTypes = $type;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPageForProductUnit(Unit\Unit $unit)
	{
		// Initially try to find the page matched against the unit's options.
		if ($page = $this->getPageForProduct($unit->product, $unit->options)) {
			return $page;
		}

		// If no page is found with these specific options, instead fallback to
		// just matching the product.
		return $this->getPageForProduct($unit->product);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPageForProduct(Product\Product $product, array $options = null)
	{
		$pages = $this->getPagesForProduct($product, $options);

		return count($pages) ? array_shift($pages) : false;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductUnitsForPage(Page\Page $page)
	{
		$return = array();

		$products = $this->getProductsForPage($page);

		$this->_unitLoader->includeInvisible(true);
		$this->_unitLoader->includeOutOfStock(true);

		foreach ($products as $product) {
			if ($units = $this->_unitLoader->getByProduct($product)) {
				$return += $units;
			}
		}

		return $return;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductForPage(Page\Page $page)
	{
		$products = $this->getProductsForPage($page);

		return count($products) ? array_shift($products) : false;
	}

	/**
	 * Load pages from a query and parameters.
	 *
	 * @param  string      $query
	 * @param  array       $params
	 * @return array[Page]
	 */
	protected function _loadPages($query, $params)
	{
		$return = [];

		$result = $this->_query->run($query, $params);

		// Filter out any pages not visible; not published or not to show on aggregator pages
		foreach ($this->_pageLoader->getByID($result->flatten()) as $key => $page) {
			// Filter out any pages that aren't viewable or published
			if (!$this->_pageAuth->isViewable($page)
			 || !$this->_pageAuth->isPublished($page)) {
				continue;
			}

			// Run custom filters and remove any where the return value is falsey
			foreach ($this->_filters as $filter) {
				if (!$filter($page)) {
					continue 2;
				}
			}

			$return[$key] = $page;
		}

		return $return;
	}

	/**
	 * Load products from a query and parameters.
	 *
	 * @param  string         $query
	 * @param  array          $params
	 * @return array[Product]
	 */
	protected function _loadProducts($query, $params)
	{
		$return = [];

		$result = $this->_query->run($query, $params);

		foreach ($this->_productLoader->getByID($result->flatten()) as $key => $product) {

			// Run custom filters and remove any where the return value is falsey
			foreach ($this->_filters as $filter) {
				if (!$filter($product)) {
					continue 2;
				}
			}

			$return[$key] = $product;
		}

		return $return;
	}
}