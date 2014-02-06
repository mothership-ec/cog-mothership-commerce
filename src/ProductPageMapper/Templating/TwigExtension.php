<?php

namespace Message\Mothership\Commerce\ProductPageMapper\Templating;

use Message\Mothership\Commerce\ProductPageMapper\ProductPageMapperInterface;
use Message\Mothership\Commerce\ProductPageMapper\PageProductMapperInterface;

use Message\Mothership\CMS\Page\Page;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;

use Twig_Extension;
use Twig_SimpleFunction;

class TwigExtension extends Twig_Extension implements ProductPageMapperInterface, PageProductMapperInterface
{
	protected $_pageMapper;
	protected $_productMapper;

	/**
	 * Constructor.
	 *
	 * @param AbstractMapper $mapper
	 */
	public function __construct(ProductPageMapperInterface $pageMapper, PageProductMapperInterface $productMapper)
	{
		$this->_pageMapper    = $pageMapper;
		$this->_productMapper = $productMapper;
	}

	/**
	 * @{inheritDoc}
	 */
	public function getFunctions()
	{
		return array(
			new Twig_SimpleFunction('getPageForProductUnit',  array($this, 'getPageForProductUnit')),
			new Twig_SimpleFunction('getPageForProduct',      array($this, 'getPageForProduct')),
			new Twig_SimpleFunction('getPagesForProduct',     array($this, 'getPagesForProduct')),
			new Twig_SimpleFunction('getProductUnitsForPage', array($this, 'getProductUnitsForPage')),
			new Twig_SimpleFunction('getProductForPage',      array($this, 'getProductForPage')),
			new Twig_SimpleFunction('getProductsForPage',     array($this, 'getProductsForPage')),
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getName()
	{
		return 'product_page_mapper';
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPageForProductUnit(Unit $unit)
	{
		return $this->_pageMapper->getPageForProductUnit($unit);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPageForProduct(Product $product, array $options = null)
	{
		return $this->_pageMapper->getPageForProduct($product, $options);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getPagesForProduct(Product $product, array $options = null)
	{
		return $this->_pageMapper->getPagesForProduct($product, $options);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductUnitsForPage(Page $page)
	{
		return $this->_productMapper->getProductUnitsForPage($page);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductForPage(Page $page)
	{
		return $this->_productMapper->getProductForPage($page);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductsForPage(Page $page)
	{
		return $this->_productMapper->getProductsForPage($page);
	}
}