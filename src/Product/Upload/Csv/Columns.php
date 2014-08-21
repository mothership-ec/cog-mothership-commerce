<?php

namespace Message\Mothership\Commerce\Product\Upload\Csv;

use Message\Mothership\Commerce\Product\Upload;
use Message\Cog\Localisation\Translator;

class Columns implements \IteratorAggregate, \Countable
{
	const NUM_VARIANTS    = 3;
	const VAR_NAME_PREFIX = 'var_name_';
	const VAR_VAL_PREFIX  = 'var_val_';

	const TRANS_PREFIX = 'ms.commerce.product.upload.csv.';

	/**
	 * @var \Message\Mothership\Commerce\Product\Upload\FieldCrawler
	 */
	private $_crawler;

	/**
	 * @var \Message\Cog\Localisation\Translator
	 */
	private $_trans;

	/**
	 * Array of column titles to appear before product type fields
	 *
	 * @var array
	 */
	private $_prefixCols = [
		'name'         => 'ms.commerce.product.upload.csv.name',
		'sort'         => 'ms.commerce.product.upload.csv.sort',
		'category'     => 'ms.commerce.product.upload.csv.category',
		'brand'        => 'ms.commerce.product.upload.csv.brand',
		'description'  => 'ms.commerce.product.upload.csv.description',
		'short_desc'   => 'ms.commerce.product.upload.csv.short_desc',
		'export_desc'  => 'ms.commerce.product.upload.csv.export_desc',
		'supplier_ref' => 'ms.commerce.product.upload.csv.supplier_ref',
		'weight'       => 'ms.commerce.product.upload.csv.weight',
		'notes'        => 'ms.commerce.product.upload.csv.notes',
		'man_country'  => 'ms.commerce.product.upload.csv.man_country',
	];

	/**
	 * Array of column titles to appear after product type fields
	 *
	 * @var array
	 */
	private $_suffixCols = [
		'price'      => 'ms.commerce.product.upload.csv.price',
		'rrp'        => 'ms.commerce.product.upload.csv.rrp',
		'cost'       => 'ms.commerce.product.upload.csv.cost',
		'tax_rate'   => 'ms.commerce.product.upload.csv.tax_rate',
	];

	private $_variantColumns = [];

	private $_productFields = [];

	private $_columns = [];

	public function __construct(Upload\FieldCrawler $crawler, Translator $trans)
	{
		$this->_crawler = $crawler;
		$this->_trans   = $trans;

		$this->_setColumns();
	}

	/**
	 * Get complete list of columns for CSV
	 *
	 * @return array
	 */
	public function getColumns()
	{
		return $this->_columns;
	}

	/**
	 * {@inheritDoc}
	 */
	public function count()
	{
		return count($this->_columns);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_columns);
	}

	/**
	 * Create an array of columns for product upload CSV
	 */
	private function _setColumns()
	{
		$this->_setVariantColumns();
		$this->_setProductFields();

		$this->_columns = $this->_prefixCols + $this->_productFields + $this->_suffixCols + $this->_variantColumns;

		$this->_validate();
		$this->_translate();

	}

	/**
	 * Pull the custom fields for the product types
	 */
	private function _setProductFields()
	{
		$this->_productFields = $this->_crawler->getFields();
	}

	/**
	 * Generate columns to allow for variants
	 */
	private function _setVariantColumns()
	{
		for ($i = 1; $i <= self::NUM_VARIANTS; $i++) {
			$varName = self::VAR_NAME_PREFIX . $i;
			$varVal  = self::VAR_VAL_PREFIX . $i;
			$this->_variantColumns[$varName] = self::TRANS_PREFIX . $varName;
			$this->_variantColumns[$varVal]  = self::TRANS_PREFIX . $varVal;
		}
	}

	/**
	 * Convert the translation keys to human readable strings
	 */
	private function _translate()
	{
		$trans = $this->_trans;

		array_walk($this->_columns, function (&$column) use ($trans) {
			$column = $trans->trans($column);
		});
	}

	/**
	 * Count the total number of columns across the sets of column arrays
	 *
	 * @return int
	 */
	private function _countColumnLists()
	{
		return count($this->_prefixCols) +
			count($this->_suffixCols) +
			count($this->_productFields) +
			count($this->_variantColumns)
		;
	}

	/**
	 * Checks that no columns have been overridden and throws an exception if this is the case
	 *
	 * @throws \LogicException
	 */
	private function _validate()
	{
		if ($this->_countColumnLists() !== $this->count()) {
			throw new \LogicException('A column has been overridden, is there a conflict in the names of the fields in a product type?');
		}
	}

}