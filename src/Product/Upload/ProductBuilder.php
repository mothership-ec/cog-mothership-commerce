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

	private $_locale;

	/**
	 * Default tax rate
	 * @todo make dynamic depending on locale
	 *
	 * @var int
	 */
	private $_defaultTaxRate = 20;

	/**
	 * Default country name
	 * @todo make dynamic depending on locale
	 *
	 * @var string
	 */
	private $_defaultManCountry = 'UK';

	private $_priceTypes = [
		'retail',
		'rrp',
		'cost',
	];

	private $_currencyID = 'GBP';

	public function __construct(
		HeadingKeys $headingKeys,
		Validate $validator,
		Product\Type\Collection $productTypes,
		Product\Type\FieldCrawler $fieldCrawler,
		UserInterface $user,
		Product\Product $product,
		Locale $locale
	)
	{
		$this->_headingKeys  = $headingKeys;
		$this->_validator    = $validator;
		$this->_productTypes = $productTypes;
		$this->_fieldCrawler = $fieldCrawler;
		$this->_user         = $user;
		$this->_product      = $product;
		$this->_locale       = $locale;
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

		$this->_product->type = $this->_productTypes->get('basic');
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
		$basePrice = null;

		foreach ($this->_priceTypes as $type) {
			$key = $this->_headingKeys->getKey($type);

			if (null === $basePrice) {
				$basePrice = $row[$key];
			}

			$price = ($row[$key]) ?: $basePrice;

			if (null === $basePrice) {
				$basePrice = $price;
			}

			$price = $this->_getPriceObject($type, $price);
			$this->_product->getPrices()->add($price);
		}
	}

	private function _getPriceObject($type, $price)
	{
		$priceObject = new Product\Price\TypedPrice($type, $this->_locale);

		$priceObject->setPrice($this->_currencyID, $price);

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
		$this->_product->taxRate = $this->_defaultTaxRate;
		$this->_product->exportManufactureCountryID = $this->_defaultManCountry;
	}
}