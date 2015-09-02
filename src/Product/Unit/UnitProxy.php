<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Localisation\Locale;
use Message\Cog\DB\Entity\EntityLoaderCollection;
use Message\Mothership\Commerce\Product\Product;

class UnitProxy extends Unit
{
	private $_loaders;

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

	public function setProductID($productID)
	{
		if (!is_numeric($productID) and $productID != (int) $productID) {
			throw new \InvalidArgumentException('Product ID must be a whole number');
		}

		$this->_productID = $productID;
	}

	public function getProductID()
	{
		return $this->_productID;
	}

	public function setProduct(Product $product)
	{
		if (null === $this->_productID) {
			$this->_productID = $product->id;
		}

		return parent::setProduct($product);
	}

	public function getProduct()
	{
		if ($this->_productID && !parent::getProduct() && $this->_loaders->exists('product')) {
			$product = $this->_loaders->get('product')->getByID($this->_productID);
			$this->setProduct($product);
		}

		return parent::getProduct();
	}

	public function __sleep()
	{
		return array_diff(array_keys(get_object_vars($this)), ['_product']);
	}

}