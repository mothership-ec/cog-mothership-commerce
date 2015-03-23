<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Product;
use Message\User\UserInterface;
use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\Authorship;

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
	 * @var \Message\Mothership\Commerce\Product\Unit\Unit
	 */
	private $_unit;

	private $_currencies;

	private $_user;

	private $_stockSetter;

	public function __construct(
		HeadingKeys $headingKeys,
		Validate $validator,
		Locale $locale,
		UserInterface $user,
		array $currencies,
		Unit $unit,
		UnitStockSetter $stockSetter
	)
	{
		$this->_headingKeys = $headingKeys;
		$this->_validator   = $validator;
		$this->_locale      = $locale;
		$this->_currencies  = $currencies;
		$this->_unit        = $unit;
		$this->_user        = $user;
		$this->_stockSetter = $stockSetter;
	}

	public function setBaseProduct(Product $product)
	{
		$this->_unit->product = $product;

		return $this;
	}

	public function build(array $row)
	{
		if (null === $this->_unit->product) {
			throw new \LogicException('Base product not set for unit creation!');
		}

		if (!$this->_validator->validateRow($row)) {
			throw new Exception\UploadException('Row is not valid!');
		}

		$this->_setOptions($row);
		$this->_setPrices($row);
		$this->_setData($row);
		$this->_addAuthorship();

		$this->_unit->visible = true;
		$this->_unit->stock = [];

		$this->_stockSetter->setStockLevel($this->_unit, $row);

		return $this->_unit;
	}

	private function _addAuthorship()
	{
		$authorship = new Authorship;
		$authorship->create(null, $this->_user);
		$this->_unit->authorship = $authorship;
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
		foreach ($this->_unit->price as $type => $price) {
			foreach ($this->_currencies as $currency) {
				$key = $this->_headingKeys->getKey($type . '.' . $currency);
				$priceVal = $row[$key];
				if ($price && $price !== $this->_getProductPrice($type, $currency)) {
					$this->_unit->setPrice($priceVal, $type, $currency);
				}
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

	private function _getProductPrice($type, $currency)
	{
		return (float) $this->_unit->getPrice($type, $currency);
	}

}