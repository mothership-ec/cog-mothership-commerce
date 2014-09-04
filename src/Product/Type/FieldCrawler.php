<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Cog\Field;
use Message\Mothership\Commerce\Product\Type;

use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class to extract the fields from a product type.
 *
 * @todo annoyingly in order to access the fields, this needs to extend the `Message\Cog\Field\Factory` class and
 * run `setFields()` on the types. This is quite blatantly a hack and inherently unstable as if the `$_fields` property
 * became private, this method would break. If anyone has any ideas of how to approach this issue please let me know
 *
 * Class FieldCrawler
 * @package Message\Mothership\Commerce\Product\Type
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class FieldCrawler extends Field\Factory
{
	const CONSTRAINT_OPTION = 'constraints';

	/**
	 * @var \Message\Mothership\Commerce\Product\Type\Collection
	 */
	private $_types;

	/**
	 * @var array
	 */
	private $_fieldNames = [];

	/**
	 * @var array
	 */
	private $_fieldDescriptions = [];

	public function __construct(Type\Collection $types)
	{
		$this->_types = $types;
	}

	/**
	 * Method for extracting names of fields from a product type
	 */
	public function getFieldNames()
	{
		if (empty($this->_fieldNames)) {
			$this->_setFieldNames();
		}

		return $this->_fieldNames;
	}

	public function getFieldDescriptions()
	{
		if (empty($this->_fieldDescriptions)) {
			$this->_setFieldDescriptions();
		}

		return $this->_fieldDescriptions;
	}

	private function _setFields()
	{
		$this->clear();

		foreach ($this->_types as $type) {
			$this->_setProductTypeFields($type);
		}

		$this->_validateFields()
		;
	}

	/**
	 * Loop through the product types and set the fields
	 */
	private function _setFieldNames()
	{
		if (empty($this->_fields)) {
			$this->_setFields();
		}

		$this->_mapFieldNames();
	}

	private function _setFieldDescriptions()
	{
		if (empty($this->_fields)) {
			$this->_setFields();
		}

		$this->_mapFieldDescriptions();
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
	private function _mapFieldNames()
	{
		$fieldNames = $this->_fields;

		array_walk($fieldNames, function (&$field) {
			$field = $field->getLabel() ?: ucfirst($field->getName());
		});

		$this->_fieldNames = $fieldNames;
	}

	private function _mapFieldDescriptions()
	{
		$descriptions = $this->_fields;

		array_walk($descriptions, function (&$field) {
			$field = $field->getDescription();
		});
	}

	private function _validateFields()
	{
		foreach ($this->_fields as $field) {
			if (!$field instanceof Field\BaseField) {
				throw new \LogicException('Field must be an instance of `Message\Cog\Field\BaseField`, ' . gettype($field) . ' given');
			}
		}

		return $this;
	}
}