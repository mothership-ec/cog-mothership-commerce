<?php

namespace Message\Mothership\Commerce\Product\Image;

/**
 * A container for order types that are available throughout the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class TypeCollection implements \IteratorAggregate, \Countable
{
	protected $_types = array();

	/**
	 * Constructor.
	 *
	 * @param array $types An array of types to add
	 */
	public function __construct(array $types = array())
	{
		foreach ($types as $type => $name) {
			$this->add($type, $name);
		}
	}

	/**
	 * Add a type to this collection.
	 *
	 * The types on this collection are sorted by type ascending immediately
	 * after the new type is added.
	 *
	 * @param string      $type Internal name for the image type
	 * @param string|null $name Display name for the image type (null to use $type)
	 *
	 * @return Collection       Returns $this for chainability
	 *
	 * @throws \InvalidArgumentException If a type with the same type has
	 *                                   already been set on this collection
	 */
	public function add($type, $name = null)
	{
		if (!$name) {
			$name = $type;
		}

		if ($this->exists($type)) {
			throw new \InvalidArgumentException(sprintf(
				'Type `%s` is already defined as `%s`',
				$type,
				$this->_types[$type]
			));
		}

		$this->_types[$type] = $name;

		return $this;
	}

	/**
	 * Get a type set on this collection by the type.
	 *
	 * @param  int $type      The type type
	 *
	 * @return GroupInterface The group instance
	 *
	 * @throws \InvalidArgumentException If the type has not been set
	 */
	public function get($type)
	{
		if (!$this->exists($type)) {
			throw new \InvalidArgumentException(sprintf('Type `%s` not set on collection', $type));
		}

		return $this->_types[$type];
	}

	/**
	 * Get all typees set on this collection, where the keys are the type
	 * types.
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->_types;
	}

	/**
	 * Check if a given type type has been defined on this collection.
	 *
	 * @param  string $type The type type
	 *
	 * @return boolean      True if it exists, false otherwise
	 */
	public function exists($type)
	{
		return array_key_exists($type, $this->_types);
	}

	/**
	 * Get the number of types registered on this collection.
	 *
	 * @return int The number of types registered
	 */
	public function count()
	{
		return count($this->_types);
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_types`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_types);
	}
}