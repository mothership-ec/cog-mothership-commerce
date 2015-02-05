<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Cog\Field;
use Message\Mothership\Commerce\Product\Type;

use Symfony\Component\Validator\Constraints;

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
	private $_fieldsPersist = [];

	/**
	 * @var array
	 */
	private $_fieldDescriptions = [];

	private $_productTypeFields = [];

	private $_constraints    = [];
	private $_constraintsSet = false;

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

	public function getTypeFields()
	{
		if (!$this->_productTypeFields) {
			$this->_setFields();
		}

		return $this->_productTypeFields;
	}

	public function getFieldDescriptions()
	{
		if (empty($this->_fieldDescriptions)) {
			$this->_setFieldDescriptions();
		}

		return $this->_fieldDescriptions;
	}

	public function getConstraints($fieldName = null)
	{
		if (!$this->_constraintsSet) {
			$this->_setFields();
		}

		if ($fieldName) {
			return (array_key_exists($fieldName, $this->_constraints)) ? $this->_constraints[$fieldName] : [];
		}

		return $this->_constraints;
	}

	private function _setFields()
	{
		$this->clear();

		foreach ($this->_types as $type) {
			$this->_setProductTypeFields($type);
		}

		$this->_setConstraints();
	}

	/**
	 * Loop through the product types and set the fields
	 */
	private function _setFieldNames()
	{
		if (empty($this->_fields)) {
			$this->_setFields();
		}

		$this->_fieldNames = $this->_mapFieldNames();
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
		$existingFields = $this->_mapFieldNames();

		$this->_savePersistentFields($type);

		$diff = array_diff($this->_mapFieldNames(), $existingFields);
		$this->_productTypeFields[$type->getName()] = $diff;
	}

	/**
	 * Convert array of field objects into an array of strings
	 */
	private function _mapFieldNames(array $fields = null)
	{
		$fieldNames = $fields ?: $this->_fieldsPersist;

		array_walk($fieldNames, function (&$field, $key) {
			$key = explode('.', $key);
			$key = array_shift($key);
			$field = ($field->getLabel() ?: ucfirst($field->getName())) . ' (' . ucfirst($key) . ')';
		});

		return $fieldNames;
	}

	private function _mapFieldDescriptions()
	{
		$descriptions = $this->_fieldsPersist;

		array_walk($descriptions, function (&$field) {
			$field = $field->getDescription();
		});
	}

	private function _setConstraints()
	{
		foreach ($this->_fieldsPersist as $field) {
			$this->_validateField($field);
			$options = $field->getFieldOptions();
			$this->_constraints[$field->getName()] = array_key_exists('constraints', $options) ? $options['constraints'] : [];
		}

		$this->_constraintsSet = true;
	}

	private function _validateField($field)
	{
		if (!$field instanceof Field\BaseField) {
			throw new \LogicException('Field must be an instance of `Message\Cog\Field\BaseField`, ' . gettype($field) . ' given');
		}
	}

	/**
	 * Adds fields on product type to the $_fields parameter, but then namespaces these with the type and adds them to the $_fieldsPersist
	 * property. This is essentially a workaround for the fact that we don't have direct access to the fields from the product type
	 * but multiple product types may have conflicting names
	 *
	 * @param ProductTypeInterface $type
	 * @throws \LogicException
	 */
	private function _savePersistentFields(Type\ProductTypeInterface $type)
	{
		$type->setFields($this);

		foreach ($this->_fields as $field) {
			$key = $type->getName() . '.' . $field->getName();
			if (array_key_exists($key, $this->_fieldsPersist)) {
				throw new \LogicException('Field with key of `' . $key . '` already exists!');
			}

			$this->_fieldsPersist[$key] = $field;
		}

		$this->clear();
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_fields`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_fieldsPersist);
	}
}