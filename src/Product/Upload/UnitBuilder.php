<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Localisation\Locale;

class UnitBuilder
{
	/**
	 * @var HeadingKeys
	 */
	private $_headingKeys;

	/**
	 * @var Validate
	 */
	private $_validator;

	/**
	 * @var Product
	 */
	private $_product;

	/**
	 * @var \Message\Mothership\Commerce\Product\Unit\Unit
	 */
	private $_unit;

	private $_priceTypes = [
		'retail',
		'rrp',
		'cost',
	];

	private $_currencyID = 'GBP';

	public function __construct(HeadingKeys $headingKeys, Validate $validator, Locale $locale)
	{
		$this->_headingKeys = $headingKeys;
		$this->_validator   = $validator;
		$this->_locale      = $locale;
		$this->_unit        = new Unit($this->_locale, $this->_priceTypes);
	}

	public function setBaseProduct(Product $product)
	{
		$this->_product = $product;
	}

	public function build(array $row)
	{
		if (null === $this->_product) {
			throw new \LogicException('Base product not set for unit creation!');
		}

		if (!$this->_validator->validateRow($row)) {
			throw new Exception\UploadException('Row is not valid!');
		}

		$this->_setOptions($row);
		$this->_setPrices($row);
		$this->_setData($row);

		return $this->_unit;
	}

	private function _setOptions(array $row)
	{
		for ($i = 1; $i <= HeadingKeys::NUM_VARIANTS; ++$i) {
			$nameKey = $this->_headingKeys->getKey(HeadingKeys::VAR_NAME_PREFIX . $i);

			if ($row[$nameKey]) {
				$valueKey = $this->_headingKeys->getKey(HeadingKeys::VAR_VAL_PREFIX . $i);
				$this->_unit->setOption($row[$nameKey], $row[$valueKey]);
			}
		}

		return $this;
	}

	private function _setPrices(array $row)
	{
		foreach ($this->_priceTypes as $type) {
			$key = $this->_headingKeys->getKey($type);
			$price = $this->_parsePrice($row[$key]);

			if ($price && $price !== $this->_getProductPrice($type)) {
				$this->_unit->setPrice($price, $type, $this->_currencyID);
			}
		}

		return $this;
	}

	private function _setData(array $row)
	{
		foreach ($row as $key => $value) {
			$key = $this->_headingKeys->getKey($key);
			if ($value && property_exists($this->_unit, $key)) {
				$this->_unit->$key = $key;
			}
		}

		return $this;
	}

	private function _getProductPrice($type)
	{
		return (float) $this->_product->getPrice($type)->getPrice($this->_currencyID, $this->_locale);
	}

	private function _parsePrice($price)
	{
		if ($price === '') {
			return null;
		}

		$price = preg_replace(self::PRICE_REGEX, '', $price);

		return (float) $price;
	}
}