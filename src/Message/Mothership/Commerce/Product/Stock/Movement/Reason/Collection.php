<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement\Reason;

/**
 * A container for stock movement reasons that are available throughout the system.
 */
class Collection implements \IteratorAggregate, \Countable
{
	protected $_reasons = array();

	/**
	 * Constructor.
	 *
	 * @param array $reasons An array of reasons to add
	 */
	public function __construct(array $reasons = array())
	{
		foreach ($reasons as $reason) {
			$this->add($reason);
		}
	}

	/**
	 * Add a reason to this collection.
	 *
	 *
	 * @param  Reason 						$reason The reason to add
	 *
	 * @return Collection 					Returns $this for chainability
	 *
	 * @throws \InvalidArgumentException 	If the reason has no name set
	 * @throws \InvalidArgumentException 	If the reason has no description set
	 * @throws \InvalidArgumentException 	If a reason with the same name has
	 *                                   	already been set on this collection
	 */
	public function add(Reason $reason)
	{
		if (!$reason->name) {
			throw new \InvalidArgumentException(sprintf('Reason `%s` has no name', $reason->description));
		}

		if (!$reason->description) {
			throw new \InvalidArgumentException(sprintf('Reason `%s` has no description', $reason->name));
		}

		if ($this->exists($reason->name)) {
			throw new \InvalidArgumentException(sprintf(
				'Reason with name `%s` is already defined as `%s`',
				$reason->name,
				$this->_reasons[$reason->name]->description
			));
		}

		$this->_reasons[$reason->name] = $reason;

		return $this;
	}

	/**
	 * Get a reason set on this collection by the name.
	 *
	 * @param  string $name 				The reason's name
	 *
	 * @return GroupInterface 				The group instance
	 *
	 * @throws \InvalidArgumentException 	If the reason has not been set
	 */
	public function get($name)
	{
		if (!$this->exists($name)) {
			throw new \InvalidArgumentException(sprintf('Reason with name `%s` not set on collection', $name));
		}

		return $this->_reasons[$name];
	}

	/**
	 * Get all reasons set on this collection, where the keys are the reason
	 * names.
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->_reasons;
	}

	/**
	 * Check if a given reason name has been defined on this collection.
	 *
	 * @param  string $name 	The reason's name
	 *
	 * @return boolean 			True if it exists, false otherwise
	 */
	public function exists($name)
	{
		return array_key_exists($name, $this->_reasons);
	}

	/**
	 * Get the number of reasons registered on this collection.
	 *
	 * @return int The number of reasons registered
	 */
	public function count()
	{
		return count($this->_reasons);
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_reasons`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_reasons);
	}
}