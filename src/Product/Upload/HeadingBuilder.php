<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product\Type\FieldCrawler;
use Message\Cog\Localisation\Translator;
use Message\Cog\FileDownload\Csv\Column;

/**
 * Class for building an array of columns
 *
 * @todo separate CSV stuff into a different class
 *
 * Class Columns
 * @package Message\Mothership\Commerce\Product\Upload\Csv
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class HeadingBuilder implements \Countable
{
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
		'name'                       => 'name',
		'sortName'                   => 'sortName',
		'category'                   => 'category',
		'brand'                      => 'brand',
		'description'                => 'description',
		'shortDescription'           => 'shortDescription',
		'exportDescription'          => 'exportDescription',
		'supplierRef'                => 'supplierRef',
		'defaultImage'               => 'defaultImage',
		'weight'                     => 'weight',
		'notes'                      => 'notes',
		'exportManufactureCountryID' => 'exportManufactureCountryID',
	];

	/**
	 * Array of column titles to appear after product type fields
	 *
	 * @var array
	 */
	private $_suffixCols = [
		'stock' => 'stock',
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
	 * Array of column headings for price types
	 *
	 * @var array
	 */
	private $_priceFields = [];

	/**
	 * Array of columns that will appear in the rendered spreadsheet
	 *
	 * @var array
	 */
	private $_columns = [];

	/**
	 * @todo move this to different class, perhaps HeadingKeys?
	 *
	 * @var array
	 */
	private $_required = [
		'name',
		'category',
	];

	/**
	 * @var array
	 */
	private $_priceTypes = [
		'cost'   =>'cost',
		'retail' => 'retail',
		'rrp'    => 'rrp',
	];

	/**
	 * @var array
	 */
	private $_currencies;

	/**
	 * @var array The dependent columns, [column => dependencies]
	 */
	private $_dependantCols = [];

	/**
	 * @todo inject column headings via HeadingKeys, caused a dependency loop so that will need working out
	 *
	 * @param FieldCrawler $crawler
	 * @param Translator $trans
	 * @param array $currencies
	 */
	public function __construct(FieldCrawler $crawler, Translator $trans, array $currencies)
	{
		$this->_crawler = $crawler;
		$this->_trans = $trans;
		$this->_currencies = $currencies;
		$this->_setPriceFields();
		$this->_required = $this->_translate($this->_required) + $this->_priceFields;
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
			count($this->_priceFields) +
			count($this->_variantColumns);
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
	 * Gets column dependencies
	 * 
	 * @return array The dependent columns, column => dependencies
	 */
	public function getColumnDependencies()
	{
		return $this->_dependantCols;
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
		for ($i = 1; $i <= HeadingKeys::NUM_VARIANTS; $i++) {
			$varName = HeadingKeys::VAR_NAME_PREFIX . $i;
			$varVal = HeadingKeys::VAR_VAL_PREFIX . $i;

			$transName = $this->_trans->trans(self::TRANS_PREFIX . HeadingKeys::VAR_NAME_PREFIX) . ' ' . $i;
			$transVal = $this->_trans->trans(self::TRANS_PREFIX . HeadingKeys::VAR_VAL_PREFIX) . ' ' . $i;

			$this->_variantColumns[$varName] = $transName;
			$this->_variantColumns[$varVal] = $transVal;

			isset($this->_dependantCols[$transName]) || $this->_dependantCols[$transName] = [];
			if (!in_array($transVal, $this->_dependantCols[$transName])) {
				$this->_dependantCols[$transName][] = $transVal;
			}

			isset($this->_dependantCols[$transVal]) || $this->_dependantCols[$transVal] = [];
			if (!in_array($transName, $this->_dependantCols[$transVal])) {
				$this->_dependantCols[$transVal][] = $transName;
			}
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
			$this->_priceFields +
			$this->_variantColumns;
	}

	/**
	 * Set price columns for spreadsheet
	 */
	private function _setPriceFields()
	{
		$priceFields = [];
		$priceTypes  = $this->_translate($this->_priceTypes);

		foreach ($priceTypes as $key => $priceType) {
			foreach ($this->_currencies as $currency) {
				$priceFields[$key . '.' . $currency] = $priceType . ' (' . $currency . ')';
			}
		}

		$this->_priceFields = $priceFields;
	}

}