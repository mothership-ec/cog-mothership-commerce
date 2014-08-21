<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Cog\Field;
use Message\Mothership\Commerce\Product\Type;

/**
 * Class to extract the fields from a product type.
 *
 * @todo annoyingly in order to access the fields, this needs to extend the `Message\Cog\Field\Factory` and
 * run `setFields()` on the types. This is quite blatantly a hack and inherently unstable as if the `$_fields` property
 * became private, this method would break. If anyone has any ideas of how to approach this issue please let me know
 *
 * Class FieldCrawler
 * @package Message\Mothership\Commerce\Product\CsvUpload
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class FieldCrawler extends Field\Factory
{
	/**
	 * @var \Message\Mothership\Commerce\Product\Type\Collection
	 */
	private $_types;

	private $_fieldNames = [];

	public function __construct(Type\Collection $types)
	{
		$this->_types = $types;
	}

	/**
	 * Method for extracting names of fields from a product type
	 */
	public function getFields()
	{
		if (empty($this->_fieldNames)) {
			$this->_setFields();
		}

		return $this->_fieldNames;
	}

	/**
	 * Loop through the product types and set the fields
	 */
	private function _setFields()
	{
		$this->clear();

		foreach ($this->_types as $type) {
			$this->_setProductTypeFields($type);
		}

		$this->_mapFields();
	}

	/**
	 * Set fields on each
	 *
	 * @param Type\ProductTypeInterface $type
	 */
	private function _setProductTypeFields(Type\ProductTypeInterface $type)
	{
		$type->setFields($this);
	}

	/**
	 * Convert array of field objects into an array of strings
	 */
	private function _mapFields()
	{
		$fieldNames = $this->_fields;

		array_walk($fieldNames, function (&$field) {
			if (!$field instanceof Field\Field) {
				throw new \LogicException('Field must be an instance of `Message\Cog\Field\Field`, ' . gettype($field) . ' given');
			}

			$field = $field->getLabel() ?: ucfirst($field->getName());
		});

		$this->_fieldNames = $fieldNames;
	}
}