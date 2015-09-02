<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Localisation\Locale;
use Message\Cog\DB\Entity\EntityLoaderCollection;

/**
 * Class UnitProxy
 * @package Message\Mothership\Commerce\Product\Unit
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Proxy class for units, so that products can be lazy loaded.
 */
class UnitProxy extends Unit
{
	/**
	 * @var EntityLoaderCollection
	 */
	private $_loaders;

	/**
	 * @var int | null
	 */
	private $_productID;

	public function __construct(
		EntityLoaderCollection $loaders,
		Locale $locale,
		array $priceTypes,
		$defaultCurrency
	)
	{
		$this->_loaders = $loaders;
		parent::__construct($locale, $priceTypes, $defaultCurrency);
	}

	/**
	 * Set the product ID so a product can be lazy loaded
	 *
	 * @param $productID
	 */
	public function setProductID($productID)
	{
		if (!is_numeric($productID) && $productID != (int) $productID) {
			throw new \InvalidArgumentException('Product ID must be a whole number');
		}

		$this->_productID = $productID;
	}

	/**
	 * Get the product ID
	 *
	 * @return int|null
	 */
	public function getProductID()
	{
		return $this->_productID;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setProduct($product)
	{
		if (null === $this->_productID) {
			$this->_productID = $product->id;
		}

		return parent::setProduct($product);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProduct()
	{
		if ($this->_productID && !parent::getProduct() && $this->_loaders->exists('product')) {
			$product = $this->_loaders->get('product')->getByID($this->_productID);
			$this->setProduct($product);
		}

		return parent::getProduct();
	}

	/**
	 * Drop product on serialization
	 */
	public function __sleep()
	{
		return array_diff(array_keys(get_object_vars($this)), ['_product']);
	}

}