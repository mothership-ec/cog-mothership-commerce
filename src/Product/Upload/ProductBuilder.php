<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Localisation\Locale;

class ProductBuilder
{
	/**
	 * @var HeadingBuilder
	 */
	private $_headingBuilder;

	/**
	 * @var Validate
	 */
	private $_validator;

	/**
	 * @var \Message\Mothership\Commerce\Product\Product
	 */
	private $_product;

	/**
	 * @var array
	 */
	private $_columns;

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

	public function __construct(HeadingBuilder $headingBuilder, Validate $validator, Locale $locale)
	{
		$this->_headingBuilder = $headingBuilder;
		$this->_columns        = $this->_headingBuilder->getColumns();
		$this->_validator      = $validator;
		$this->_product        = new Product($locale);
	}

	public function build(array $data)
	{
		if (!$this->_validator->validateRow($data)) {
			throw new Exception\UploadException('Row is not valid!');
		}

		$this->_addData($data);
	}

	private function _addData(array $data)
	{
		foreach ($data as $key => $value) {
			$key = $this->_getKey($key);
			if ($value !== '' && property_exists($this->_product, $key)) {
				$this->_product->$key = $value;
			}
		}
	}

	private function _getKey($key)
	{
		if (!array_key_exists($key, $this->_columns)) {
			throw new \LogicException('Column `' . $key . '` does not exist!');
		}

		return $this->_columns[$key];
	}
}