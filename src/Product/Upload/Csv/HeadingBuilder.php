<?php

namespace Message\Mothership\Commerce\Product\Upload\Csv;

use Message\Mothership\Commerce\Product\Type\FieldCrawler;
use Message\Cog\Localisation\Translator;
use Message\Cog\FileDownload\Csv\Column;

/**
 * Class for building an array of columns
 *
 * Class Columns
 * @package Message\Mothership\Commerce\Product\Upload\Csv
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class HeadingBuilder implements \Countable
{
	const NUM_VARIANTS    = 3;
	const VAR_NAME_PREFIX = 'var_name.name';
	const VAR_VAL_PREFIX  = 'var_val.name';

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

	private $_required = [
		'name',
		'category',
		'price'
	];

	/**
	 * Array of columns for unit variants
	 *
	 * @var array
	 */
	private $_variantColumns = [];

	/**
	 * Array of column headings that are pulled from the registered product types
	 *
	 * @var array
	 */
	private $_productFields = [];

	/**
	 * Array of columns that will appear in the rendered spreadsheet
	 *
	 * @var array
	 */
	private $_columns = [];

	public function __construct(FieldCrawler $crawler, Translator $trans)
	{
		$this->_crawler = $crawler;
		$this->_trans   = $trans;
		$this->_required = $this->_translate($this->_required);
	}

	/**
	 * Get complete list of columns for CSV
	 *
	 * @return array
	 */
	public function getColumns()
	{
		$this->_setColumns();

		return $this->_columns;
	}

	/**
	 * Count the total number of columns across the sets of column arrays
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->_prefixCols) +
			count($this->_suffixCols) +
			count($this->_productFields) +
			count($this->_variantColumns)
		;
	}

	/**
	 * Return array of required fields
	 *
	 * @return array
	 */
	public function getRequired()
	{
		return $this->_required;
	}

	/**
	 * Create an array of columns for product upload CSV
	 */
	private function _setColumns()
	{
		$this->_setVariantColumns();
		$this->_setProductFields();

		$this->_columns = $this->_getHeadingColumns();

		$this->_parseColumns()
			->_validate();
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
			$this->_variantColumns[$varName] = $this->_trans->trans(self::TRANS_PREFIX . self::VAR_NAME_PREFIX) . ' ' . $i;
			$this->_variantColumns[$varVal]  = $this->_trans->trans(self::TRANS_PREFIX . self::VAR_VAL_PREFIX) . ' ' . $i;
		}
	}

	/**
	 * Convert an array of translation keys to human readable strings
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	private function _translate(array $columns)
	{
		$trans = $this->_trans;

		array_walk($columns, function (&$column) use ($trans) {
			$column = $trans->trans(self::TRANS_PREFIX . $column . self::TRANS_NAME_SUFFIX);
		});

		return $columns;
	}

	private function _parseColumns()
	{
		array_walk($this->_columns, function (&$column) {
			$column = new Column($column);
		});

		return $this;
	}

	/**
	 * Checks that no columns have been overridden and throws an exception if this is the case
	 *
	 * @throws \LogicException
	 */
	private function _validate()
	{
		if (count($this->_columns) !== $this->count()) {
			throw new \LogicException('A column has been overridden, is there a conflict in the names of the fields in a product type?');
		}
	}

	/**
	 * Get all the column headings
	 *
	 * @return array
	 */
	private function _getHeadingColumns()
	{
		return $this->_translate($this->_prefixCols) +
			$this->_productFields +
			$this->_translate($this->_suffixCols) +
			$this->_variantColumns;
	}

}