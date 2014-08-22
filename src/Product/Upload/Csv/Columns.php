<?php

namespace Message\Mothership\Commerce\Product\Upload\Csv;

use Message\Mothership\Commerce\Product\Type\FieldCrawler;
use Message\Cog\Localisation\Translator;

class Columns implements \IteratorAggregate, \Countable
{
	const NUM_VARIANTS    = 3;
	const VAR_NAME_PREFIX = 'var_name_';
	const VAR_VAL_PREFIX  = 'var_val_';

	const TRANS_PREFIX = 'ms.commerce.product.upload.csv.';
	const TRANS_NAME_SUFFIX = '.name';
	const TRANS_NAME_PREFIX = '.help';

	/**
	 * @var \Message\Mothership\Commerce\Product\Type\FieldCrawler
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
		'name'         => 'name',
		'sort'         => 'sort',
		'category'     => 'category',
		'brand'        => 'brand',
		'description'  => 'description',
		'short_desc'   => 'short_desc',
		'export_desc'  => 'export_desc',
		'supplier_ref' => 'supplier_ref',
		'weight'       => 'weight',
		'notes'        => 'notes',
		'man_country'  => 'man_country',
	];

	/**
	 * Array of column titles to appear after product type fields
	 *
	 * @var array
	 */
	private $_suffixCols = [
		'price'    => 'price',
		'rrp'      => 'rrp',
		'cost'     => 'cost',
		'tax_rate' => 'tax_rate',
	];

	private $_variantColumns = [];

	private $_productFields = [];

	private $_columns = [];

	public function __construct(FieldCrawler $crawler, Translator $trans)
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

		$this->_columns = $this->_getHeadingColumns();

		$this->_validate();
		$this->_translate();

	}

	/**
	 * Pull the custom fields for the product types
	 */
	private function _setProductFields()
	{
		$this->_productFields = $this->_crawler->getFieldNames();
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
			$column = $trans->trans(self::TRANS_PREFIX . $column . self::TRANS_NAME_SUFFIX);
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

	private function _getHeadingColumns()
	{
		return $this->_prefixCols + $this->_productFields + $this->_suffixCols + $this->_variantColumns;
	}

}