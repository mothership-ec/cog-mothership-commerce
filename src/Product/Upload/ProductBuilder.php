<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product;
use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\Authorship;
use Message\User\UserInterface;

class ProductBuilder
{
	const DEFAULT_TYPE = 'basic';

	/**
	 * @var HeadingKeys
	 */
	private $_headingKeys;

	/**
	 * @var Validate
	 */
	private $_validator;

	/**
	 * @var \Message\Mothership\Commerce\Product\Product
	 */
	private $_product;

	/**
	 * @var \Message\Mothership\Commerce\Product\Type\Collection
	 */
	private $_productTypes;

	/**
	 * @var \Message\Mothership\Commerce\Product\Type\FieldCrawler
	 */
	private $_fieldCrawler;

	/**
	 * @var \Message\Cog\Localisation\Locale
	 */
	private $_locale;

	/**
	 * @var array
	 */
	private $_currencies;

	/**
	 * @var
	 */
	private $_defaultCurrency;

	/**
	 * @var string
	 */
	private $_defaultManCountry = 'GB';

	private $_priceTypes = [
		'retail',
		'rrp',
		'cost',
	];

	public function __construct(
		HeadingKeys $headingKeys,
		Validate $validator,
		Product\Type\Collection $productTypes,
		Product\Type\FieldCrawler $fieldCrawler,
		UserInterface $user,
		Product\Product $product,
		Locale $locale,
		array $currencies,
		$defaultCurrency,
		$defaultManCountry
	)
	{
		$this->_headingKeys       = $headingKeys;
		$this->_validator         = $validator;
		$this->_productTypes      = $productTypes;
		$this->_fieldCrawler      = $fieldCrawler;
		$this->_user              = $user;
		$this->_product           = $product;
		$this->_locale            = $locale;
		$this->_currencies        = $currencies;
		$this->_defaultCurrency   = $defaultCurrency;
		$this->_defaultManCountry = $defaultManCountry ?: $this->_defaultManCountry;
	}

	public function build(array $data)
	{
		if (!$this->_validator->validateRow($data)) {
			throw new Exception\UploadException('Row is not valid!');
		}

		$this->_addProductType($data);
		$this->_addAuthorship();
		$this->_addData($data);
		$this->_setPrices($data);

		return $this->_product;
	}

	private function _addAuthorship()
	{
		$authorship = new Authorship;
		$authorship->create(null, $this->_user);
		$this->_product->authorship = $authorship;
	}

	private function _addProductType(array $data)
	{
		foreach ($this->_fieldCrawler->getTypeFields() as $type => $fields) {
			foreach ($fields as $name) {
				if (!empty($data[$name])) {
					$this->_product->type = $this->_productTypes->get($type);
					$this->_setDetails($data);

					return;
				}
			}
		}

		$this->_product->type = $this->_productTypes->getDefault();
	}

	private function _setDetails(array $data)
	{
		$this->_fieldCrawler->build($this->_product->type);
		$details = new Product\Type\DetailCollection;

		foreach ($this->_fieldCrawler as $name => $field) {
			$field->setValue($data[$this->_headingKeys->getKey($name)]);
			$details->$name	= $field;
		}

		$this->_product->details = $details;
	}

	private function _setPrices(array $row)
	{
		$default = null;
		foreach ($this->_priceTypes as $type) {
			$price = $this->_getPriceObject($type, $row, $default);
			if (null === $default && $type === 'retail') {
				$default = $price;
			}
			$this->_product->getPrices()->add($price);
		}
	}

	private function _getPriceObject($type, array $row, $default = null)
	{
		$priceObject = new Product\Price\TypedPrice($type, $this->_locale);

		foreach ($this->_currencies as $currency) {
			$key = $this->_headingKeys->getKey($type . '.' . $currency);
			$price = (!$row[$key] && $default instanceof Product\Price\TypedPrice) ? $default->getPrice($currency) : $row[$key];
			$priceObject->setPrice($currency, $price);
		}

		return $priceObject;
	}

	private function _addData(array $data)
	{
		$this->_addProductDefaults();

		foreach ($data as $key => $value) {
			$key = $this->_headingKeys->getKey($key);
			if ($value !== '' && property_exists($this->_product, $key)) {
				$this->_product->$key = $value;
			}
		}
	}

	private function _addProductDefaults()
	{
		$this->_product->exportManufactureCountryID = $this->_defaultManCountry;
	}
}