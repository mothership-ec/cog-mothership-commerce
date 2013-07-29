<?php

namespace Message\Mothership\Commerce\Product\ImageType;

/**
 * A container for order typees that are available throughout the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Collection implements \IteratorAggregate, \Countable
{
	protected $_types = array();

	/**
	 * Constructor.
	 *
	 * @param array $typees An array of typees to add
	 */
	public function __construct(array $types = array())
	{
		foreach ($types as $type) {
			$this->add($type);
		}
	}

	/**
	 * Add a type to this collection.
	 *
	 * The typees on this collection are sorted by type ascending immediately
	 * after the new type is added.
	 *
	 * @param type $type The type to add
	 *
	 * @return Collection    Returns $this for chainability
	 *
	 * @throws \InvalidArgumentException If the type has no type set
	 * @throws \InvalidArgumentException If a type with the same type has
	 *                                   already been set on this collection
	 */
	public function add(ImageType $type)
	{
		if (!$type->type && 0 !== $type->type) {
			throw new \InvalidArgumentException(sprintf('type `%s` has no type', $type->type));
		}

		if ($this->exists($type->type)) {
			throw new \InvalidArgumentException(sprintf(
				'type type `%i` is already defined as `%s`',
				$type->type,
				$this->_types[$type->type]->type
			));
		}

		$this->_types[$type->type] = $type;

		ksort($this->_types);

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
			throw new \InvalidArgumentException(sprintf('type type `%i` not set on collection', $type));
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
	 * @param  int $type The type type
	 *
	 * @return boolean   True if it exists, false otherwise
	 */
	public function exists($type)
	{
		return array_key_exists($type, $this->_types);
	}

	/**
	 * Get the number of typees registered on this collection.
	 *
	 * @return int The number of typees registered
	 */
	public function count()
	{
		return count($this->_types);
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the >_types`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_types);
	}
}