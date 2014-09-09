<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product;
use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\Authorship;
use Message\User\UserInterface;

class ProductBuilder
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
	 * @var \Message\Mothership\Commerce\Product\Product
	 */
	private $_product;

	/**
	 * @var \Message\Mothership\Commerce\Product\Type\Collection
	 */
	private $_productTypes;

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

	public function __construct(
		HeadingKeys $headingKeys,
		Validate $validator,
		Product\Type\Collection $productTypes,
		Locale $locale,
		UserInterface $user
	)
	{
		$this->_headingKeys  = $headingKeys;
		$this->_validator    = $validator;
		$this->_productTypes = $productTypes;
		$this->_locale       = $locale;
		$this->_user         = $user;
		$this->_product      = new Product\Product($locale);
	}

	public function build(array $data)
	{
		if (!$this->_validator->validateRow($data)) {
			throw new Exception\UploadException('Row is not valid!');
		}

		$this->_addProductType($data);
		$this->_addAuthorship();
		$this->_addData($data);

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
		$this->_product->type = $this->_productTypes->get('basic');
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