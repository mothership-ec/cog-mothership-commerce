<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Localisation\Locale;
use Message\Cog\DB\Entity\EntityLoaderCollection;

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
		if (!is_numeric($productID)) {
			throw new \InvalidArgumentException('Product ID must be numeric');
		}
	}

	public function getProduct()
	{
		if ($this->_productID && !parent::getProduct() && $this->_loaders->exists('product')) {
			$product = $this->_loaders->get('product')->getByID($this->_productID);
			$this->setProduct($product);
		}

		return parent::getProduct();
	}

}